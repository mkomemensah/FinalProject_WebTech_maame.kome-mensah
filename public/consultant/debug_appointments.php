<?php
// Debug script to check appointments
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
require_once __DIR__ . '/../../app/config/database.php';

header('Content-Type: text/plain');

echo "=== APPOINTMENTS DEBUG ===\n\n";
echo "Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n\n";

// Check consultant lookup
$stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row = $stmt->fetch();
$consultant_id = $row ? $row['consultant_id'] : null;

echo "Consultant ID: " . ($consultant_id ?? 'NOT FOUND') . "\n\n";

if ($consultant_id) {
    // Check total appointments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE consultant_id = ?");
    $stmt->execute([$consultant_id]);
    $total = $stmt->fetchColumn();
    echo "Total appointments in database: $total\n\n";
    
    // List all appointments with their status
    $stmt = $pdo->prepare("SELECT appointment_id, status, client_id, availability_id, created_at FROM appointments WHERE consultant_id = ? ORDER BY created_at DESC");
    $stmt->execute([$consultant_id]);
    $allAppts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "All appointments:\n";
    foreach ($allAppts as $apt) {
        echo "  - ID: {$apt['appointment_id']}, Status: {$apt['status']}, Client ID: {$apt['client_id']}, Availability ID: {$apt['availability_id']}\n";
    }
    echo "\n";
    
    // Check which appointments have availability records
    $stmt = $pdo->prepare("SELECT a.appointment_id, a.status, av.availability_id as av_id 
                           FROM appointments a 
                           LEFT JOIN availability av ON a.availability_id = av.availability_id 
                           WHERE a.consultant_id = ?");
    $stmt->execute([$consultant_id]);
    $withAvail = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Appointments with availability:\n";
    foreach ($withAvail as $apt) {
        echo "  - Appointment ID: {$apt['appointment_id']}, Status: {$apt['status']}, Availability ID: " . ($apt['av_id'] ?? 'NULL') . "\n";
    }
    echo "\n";
    
    // Try the actual query
    $sql = "SELECT a.*, 
                   av.date AS date, 
                   av.start_time AS start_time, 
                   av.end_time AS end_time, 
                   u.user_id AS client_user_id, 
                   u.name AS client_name, 
                   u.email AS client_email
            FROM appointments a
            JOIN availability av ON a.availability_id = av.availability_id
            JOIN users u ON a.client_id = u.user_id
            WHERE a.consultant_id = ?
            ORDER BY av.date DESC, av.start_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$consultant_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query result count: " . count($result) . "\n";
    if (count($result) > 0) {
        echo "First result: " . json_encode($result[0], JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "ERROR: Could not find consultant_id for user_id: " . $_SESSION['user_id'] . "\n";
    echo "Checking consultants table:\n";
    $stmt = $pdo->query("SELECT consultant_id, user_id FROM consultants");
    $allConsultants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allConsultants as $c) {
        echo "  - Consultant ID: {$c['consultant_id']}, User ID: {$c['user_id']}\n";
    }
}

