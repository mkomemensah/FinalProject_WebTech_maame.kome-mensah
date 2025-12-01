<?php
require_once __DIR__ . '/../config/database.php';

class AdminController {
    public static function addExpertise($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO expertise (name, description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['description']]);
        return ['success'=>true];
    }
    // Implement manage_users, approve/suspend, etc.
}