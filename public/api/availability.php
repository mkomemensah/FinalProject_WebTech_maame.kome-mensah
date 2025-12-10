<?php
require_once __DIR__ . '/../../app/controllers/ConsultantController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'set':
        require_role('consultant');
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
        // If ?consultant_id is set, list their available slots (public)
        if (isset($_GET['consultant_id'])) {
            $slots = ConsultantController::getAvailabilityPublic($_GET['consultant_id']);
            header('Content-Type: application/json');
            echo json_encode($slots);
            break;
        }
        // fallback: consultant can see their slots (private)
        require_role('consultant');
        $slots = ConsultantController::getAvailability($_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode($slots);
        break;
}