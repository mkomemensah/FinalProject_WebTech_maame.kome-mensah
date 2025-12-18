<?php
// Test endpoint to debug appointments
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
require_once __DIR__ . '/../../app/config/database.php';

header('Content-Type: application/json');

$debug = [];

// Get session info
$debug['session'] = [
    'user_id' => $_SESSION['user_id'] ?? 'not set',
    'role' => $_SESSION['role'] ?? 'not set'
];

// Find consultant_id
$stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row = $stmt->fetch();
$consultant_id = $row ? $row['consultant_id'] : null;

$debug['consultant_lookup'] = [
    'user_id' => $_SESSION['user_id'],
    'consultant_id' => $consultant_id,
    'found' => $consultant_id !== null
];

if ($consultant_id) {
    // Count all appointments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE consultant_id = ?");
    $stmt->execute([$consultant_id]);
    $total = $stmt->fetchColumn();
    $debug['total_appointments'] = $total;
    
    // Count by status
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE consultant_id = ? GROUP BY status");
    $stmt->execute([$consultant_id]);
    $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['appointments_by_status'] = $byStatus;
    
    // Get all appointments (simple query)
    $stmt = $pdo->prepare("SELECT appointment_id, status, client_id, availability_id, created_at FROM appointments WHERE consultant_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$consultant_id]);
    $allAppts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['sample_appointments'] = $allAppts;
    
    // Check availability records
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments a LEFT JOIN availability av ON a.availability_id = av.availability_id WHERE a.consultant_id = ? AND av.availability_id IS NOT NULL");
    $stmt->execute([$consultant_id]);
    $withAvail = $stmt->fetchColumn();
    $debug['appointments_with_availability'] = $withAvail;
    
    // Check client records
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments a LEFT JOIN users u ON a.client_id = u.user_id WHERE a.consultant_id = ? AND u.user_id IS NOT NULL");
    $stmt->execute([$consultant_id]);
    $withClient = $stmt->fetchColumn();
    $debug['appointments_with_client'] = $withClient;
    
    // Try the actual query (without business_problems since table might not exist)
    $sql = "SELECT a.*, 
                   COALESCE(av.date, '') AS date, 
                   COALESCE(av.start_time, '') AS start_time, 
                   COALESCE(av.end_time, '') AS end_time, 
                   COALESCE(u.user_id, a.client_id) AS client_user_id, 
                   COALESCE(u.name, 'Unknown Client') AS client_name, 
                   COALESCE(u.email, '') AS client_email,
                   COALESCE(f.consultant_notes, '') AS consultant_notes, 
                   COALESCE(f.client_notes, '') AS client_notes,
                   '' AS business_problem
            FROM appointments a
            LEFT JOIN availability av ON a.availability_id = av.availability_id
            LEFT JOIN users u ON a.client_id = u.user_id
            LEFT JOIN feedback f ON f.appointment_id = a.appointment_id
            WHERE a.consultant_id = ?
            ORDER BY 
              CASE a.status 
                WHEN 'pending' THEN 1 
                WHEN 'confirmed' THEN 2 
                WHEN 'completed' THEN 3 
                WHEN 'cancelled' THEN 4 
                ELSE 5 
              END,
              COALESCE(av.date, '1970-01-01') DESC, 
              COALESCE(av.start_time, '00:00:00') DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$consultant_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $debug['query_error'] = $e->getMessage();
        $result = [];
    }
    $debug['query_result_count'] = count($result);
    $debug['query_result_sample'] = count($result) > 0 ? $result[0] : null;
} else {
    $debug['error'] = 'Consultant ID not found';
}

echo json_encode($debug, JSON_PRETTY_PRINT);

