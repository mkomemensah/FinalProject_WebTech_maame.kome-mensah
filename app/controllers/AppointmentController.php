<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/validators.php';

class AppointmentController {
    // Book appointment
    public static function book($data) {
        global $pdo;
        // Validation here
        $stmt = $pdo->prepare("INSERT INTO appointments (client_id, consultant_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
        $stmt->execute([$data['client_id'], $data['consultant_id']]);
        return ['success' => true];
    }
    public static function getClientAppointments($client_id) {/*...*/}
    public static function getConsultantAppointments($consultant_id) {/*...*/}
    public static function getAllAppointments() {/*...*/}
    public static function submitProblem($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO business_problems (appointment_id, description) VALUES (?, ?)");
        $stmt->execute([$data['appointment_id'], $data['description']]);
        return ['success'=>true];
    }
    public static function submitFeedback($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO feedback (appointment_id, consultant_notes) VALUES (?, ?)");
        $stmt->execute([$data['appointment_id'], $data['consultant_notes']]);
        return ['success'=>true];
    }
}