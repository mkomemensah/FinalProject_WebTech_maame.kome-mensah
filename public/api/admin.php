<?php
require_once __DIR__ . '/../../app/controllers/AdminController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
require_role('admin');
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_expertise':
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description']
        ];
        $result = AdminController::addExpertise($data);
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
    // Add: suspend_user, approve_consultant, etc.
}