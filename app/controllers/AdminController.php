<?php
require_once __DIR__ . '/../config/database.php';

class AdminController {
    public static function addExpertise($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO expertise (name, description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['description']]);
        return ['success'=>true];
    }
    // List users with pagination and optional search
    public static function listUsers($page = 1, $perPage = 25, $search = ''){
        global $pdo;
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where = "";
        if($search){
            $where = "WHERE name LIKE ? OR email LIKE ?";
            $like = "%" . $search . "%";
            $params[] = $like; $params[] = $like;
        }
        $countSql = "SELECT COUNT(*) as cnt FROM users " . $where;
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // LIMIT/OFFSET can't reliably be bound as parameters on some MySQL/MariaDB drivers
        // so interpolate them as integers after casting to avoid SQL injection.
        $perPageInt = (int)$perPage;
        $offsetInt = (int)$offset;
        $sql = "SELECT user_id, name, email, role, status, phone, created_at FROM users " . $where . " ORDER BY name ASC LIMIT " . $perPageInt . " OFFSET " . $offsetInt;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['success'=>true, 'users'=>$users, 'total'=>$total, 'page'=>$page, 'per_page'=>$perPage];
    }

    public static function getUser($userId){
        global $pdo;
        $stmt = $pdo->prepare("SELECT user_id, name, email, role, status, phone, created_at FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$u) return ['success'=>false, 'error'=>'User not found'];
        return ['success'=>true, 'user'=>$u];
    }

    public static function updateUserStatus($userId, $status){
        global $pdo;
        $allowed = ['active','suspended','pending'];
        if(!in_array($status, $allowed)) return ['success'=>false, 'error'=>'Invalid status'];
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->execute([$status, $userId]);
        return ['success'=>true];
    }
    // Note: soft-delete/restore methods removed. Use updateUserStatus(user_id, 'suspended'|'active')

    public static function approveConsultant($consultantId){
        global $pdo;
        $stmt = $pdo->prepare("UPDATE consultants SET profile_status = 'approved' WHERE consultant_id = ?");
        $stmt->execute([$consultantId]);
        return ['success'=>true];
    }
}