<?php
require_once __DIR__ . '/../config/database.php';

class ConsultantController {
    public static function getAllPublic() {/* ... fetch approved only ... */}
    public static function updateProfile($data) {/* ... */}
    public static function addAvailability($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO availability (consultant_id, date, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['consultant_id'], $data['date'], $data['start_time'], $data['end_time']]);
        return ['success'=>true];
    }
    public static function getAvailability($consultant_id) {/* ... */}
}