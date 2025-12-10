<?php
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
require_role('consultant');
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'submit':
        if (isset($_POST['consultant_notes'])) {
            require_role('consultant');
            $data = [
                'appointment_id' => $_POST['appointment_id'],
                'consultant_notes' => $_POST['consultant_notes'],
                'consultant_id' => $_SESSION['user_id']
            ];
            $result = AppointmentController::submitFeedback($data);
        } else if (isset($_POST['client_notes'])) {
            require_role('client');
            $data = [
                'appointment_id' => $_POST['appointment_id'],
                'client_notes' => $_POST['client_notes'],
                'client_id' => $_SESSION['user_id']
            ];
            $result = AppointmentController::submitClientFeedback($data);
        } else {
            $result = ['success'=>false,'error'=>'No feedback provided.'];
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
}