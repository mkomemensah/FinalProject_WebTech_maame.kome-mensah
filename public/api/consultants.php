<?php
require_once __DIR__ . '/../../app/controllers/ConsultantController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';

secure_session_start();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        // For consultant browsing
        $consultants = ConsultantController::getAllPublic();
        header('Content-Type: application/json');
        echo json_encode($consultants);
        break;
    case 'update_profile':
        require_once __DIR__ . '/../../app/middleware/role_middleware.php';
        require_role('consultant');
        $data = [
            'user_id' => $_SESSION['user_id'],
            'bio' => $_POST['bio'],
            'years_of_experience' => $_POST['years_of_experience'],
        ];
        $result = ConsultantController::updateProfile($data);
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
}