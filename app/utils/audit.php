<?php
// app/utils/audit.php
require_once __DIR__ . '/../config/database.php';

function write_audit($admin_user_id, $action, $target_type = null, $target_id = null, $details = null) {
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO admin_audit (admin_user_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$admin_user_id, $action, $target_type, $target_id, $details, $ip]);
        return true;
    } catch (Exception $e) {
        // Swallow errors to avoid breaking admin flows; optionally log to file
        error_log('Audit write failed: ' . $e->getMessage());
        return false;
    }
}

function fetch_audit($limit = 200) {
    global $pdo;
    $limitInt = (int)$limit;
    // Some MySQL/MariaDB drivers quote bound LIMIT params which causes syntax errors.
    // Interpolate the integer after casting to avoid that problem safely.
    $sql = "SELECT aa.*, u.name as admin_name, u.email as admin_email FROM admin_audit aa JOIN users u ON aa.admin_user_id = u.user_id ORDER BY aa.created_at DESC LIMIT " . $limitInt;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If the admin_audit table doesn't exist or another DB issue occurs,
        // don't let the whole page crash. Log and return an empty array.
        error_log('fetch_audit failed: ' . $e->getMessage());
        return [];
    }
}

?>
