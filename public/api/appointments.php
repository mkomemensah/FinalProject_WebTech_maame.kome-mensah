<?php
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'book':
        require_role('client');
        $data = [
            'consultant_id' => $_POST['consultant_id'],
            'date' => $_POST['date'],
            'time' => $_POST['time'],
            'client_id' => $_SESSION['user_id'],
        ];
        $result = AppointmentController::book($data);
        header('Content-Type: application/json');
        echo json_encode($result); // ['success'=>bool, 'errors'=>[]]
        break;

    case 'list':
        // AJAX table fill for clients/consultants/admins
        if ($_SESSION['role'] === 'client') {
            $appointments = AppointmentController::getClientAppointments($_SESSION['user_id']);
        } elseif ($_SESSION['role'] === 'consultant') {
            $appointments = AppointmentController::getConsultantAppointments($_SESSION['user_id']);
        } elseif ($_SESSION['role'] === 'admin') {
            $appointments = AppointmentController::getAllAppointments();
        } else {
            $appointments = [];
        }
        header('Content-Type: application/json');
        echo json_encode($appointments);
        break;
    // add cancel/confirm endpoints as needed
}