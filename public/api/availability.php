<?php
require_once __DIR__ . '/../../app/controllers/ConsultantController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
require_role('consultant');
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'set':
        $data = [
            'consultant_id' => $_SESSION['user_id'],
            'date' => $_POST['date'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
        ];
        $result = ConsultantController::addAvailability($data);
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
    case 'list':
        // fill for AJAX calendar
        $slots = ConsultantController::getAvailability($_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode($slots);
        break;
}