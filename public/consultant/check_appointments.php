<?php
// Simple check script - run this directly in browser
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
require_once __DIR__ . '/../../app/config/database.php';

echo "<h2>Appointments Debug Check</h2>";
echo "<pre>";

echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "Role: " . $_SESSION['role'] . "\n\n";

// Find consultant_id
$stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row = $stmt->fetch();
$consultant_id = $row ? $row['consultant_id'] : null;

echo "Consultant ID: " . ($consultant_id ?? 'NOT FOUND') . "\n\n";

if ($consultant_id) {
    // Count all appointments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE consultant_id = ?");
    $stmt->execute([$consultant_id]);
    $total = $stmt->fetchColumn();
    echo "Total appointments in database: $total\n\n";
    
    if ($total > 0) {
        // Show by status
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE consultant_id = ? GROUP BY status");
        $stmt->execute([$consultant_id]);
        $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Appointments by status:\n";
        foreach ($byStatus as $stat) {
            echo "  - {$stat['status']}: {$stat['count']}\n";
        }
        echo "\n";
        
        // Show sample appointments
        $stmt = $pdo->prepare("SELECT appointment_id, status, client_id, availability_id, created_at FROM appointments WHERE consultant_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$consultant_id]);
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample appointments (last 5):\n";
        foreach ($samples as $apt) {
            echo "  ID: {$apt['appointment_id']}, Status: {$apt['status']}, Client ID: {$apt['client_id']}, Availability ID: {$apt['availability_id']}\n";
        }
        echo "\n";
        
        // Check availability
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments a LEFT JOIN availability av ON a.availability_id = av.availability_id WHERE a.consultant_id = ? AND av.availability_id IS NULL");
        $stmt->execute([$consultant_id]);
        $noAvail = $stmt->fetchColumn();
        echo "Appointments WITHOUT availability records: $noAvail\n\n";
        
        // Try the actual query
        $sql = "SELECT a.*, 
                       COALESCE(av.date, '') AS date, 
                       COALESCE(av.start_time, '') AS start_time, 
                       COALESCE(av.end_time, '') AS end_time, 
                       COALESCE(u.user_id, a.client_id) AS client_user_id, 
                       COALESCE(u.name, 'Unknown Client') AS client_name, 
                       COALESCE(u.email, '') AS client_email
                FROM appointments a
                LEFT JOIN availability av ON a.availability_id = av.availability_id
                LEFT JOIN users u ON a.client_id = u.user_id
                WHERE a.consultant_id = ?
                LIMIT 5";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$consultant_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Query result count: " . count($result) . "\n";
        if (count($result) > 0) {
            echo "First result:\n";
            print_r($result[0]);
        } else {
            echo "ERROR: Query returned 0 results but database has $total appointments!\n";
            echo "This means the JOINs are filtering out all appointments.\n";
        }
    } else {
        echo "No appointments found in database for this consultant.\n";
        echo "This means appointments are not being saved when you accept bookings.\n";
    }
} else {
    echo "ERROR: Could not find consultant_id for user_id " . $_SESSION['user_id'] . "\n";
    echo "This means your user account is not linked to a consultant record.\n";
}

echo "</pre>";

