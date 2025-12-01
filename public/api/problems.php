<?php
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
require_role('client');
$action = $_GET['action'] ?? '';

switch($action) {
    case 'submit':
        $data = [
            'appointment_id' => $_POST['appointment_id'] ?? null,
            'description' => $_POST['description'],
            'client_id' => $_SESSION['user_id'],
        ];
        $result = AppointmentController::submitProblem($data);
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
}