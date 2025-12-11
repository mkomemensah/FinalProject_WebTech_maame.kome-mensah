<?php
$require_path = __DIR__ . '/../../app/';
require_once $require_path . 'controllers/AdminController.php';
// Use session util directly so API can return JSON errors instead of HTML redirects
require_once $require_path . 'utils/session.php';
secure_session_start();
// If not logged in or not admin, return JSON error (AJAX-friendly)
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(['success'=>false,'error'=>'Insufficient privileges']);
    exit;
}
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
    case 'list_users':
        $page = isset($_GET['page'])? (int)$_GET['page'] : 1;
        $per = isset($_GET['per'])? (int)$_GET['per'] : 25;
        $search = $_GET['search'] ?? '';
        $result = AdminController::listUsers($page, $per, $search);
        header('Content-Type: application/json'); echo json_encode($result); break;
    case 'get_user':
        $id = $_GET['id'] ?? 0; $result = AdminController::getUser($id); header('Content-Type: application/json'); echo json_encode($result); break;
    case 'update_user_status':
        $id = $_POST['id'] ?? 0; $status = $_POST['status'] ?? ''; $result = AdminController::updateUserStatus($id, $status); header('Content-Type: application/json'); echo json_encode($result); break;
    // delete_user and restore_user actions removed — use update_user_status to suspend/activate accounts
    case 'approve_consultant':
        $id = $_POST['consultant_id'] ?? 0; $result = AdminController::approveConsultant($id); header('Content-Type: application/json'); echo json_encode($result); break;
    // Add: suspend_user, approve_consultant, etc.
}