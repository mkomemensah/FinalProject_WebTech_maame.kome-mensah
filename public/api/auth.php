<?php
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/controllers/ClientController.php'; // Added for update_profile
require_once __DIR__ . '/../../app/middleware/role_middleware.php'; // Ensure require_role() is available

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role' => $_POST['role'] ?? 'client'
        ];
        $errors = AuthController::register($data);
        if ($errors) {
            // If AJAX, echo json; else, redirect with errors
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $errors]);
        } else {
            header('Location: ' . BASE_URL . 'login.php?registered=1');
        }
        exit;
    case 'login':
        $data = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];
        $errors = AuthController::login($data);
        if ($errors) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $errors]);
        } else {
            // Redirect by role
            $role = $_SESSION['role'];
            if ($role == 'client') $to = BASE_URL . 'client/dashboard.php';
            elseif ($role == 'consultant') $to = BASE_URL . 'consultant/dashboard.php';
            else $to = BASE_URL . 'admin/dashboard.php';
            header('Location: ' . $to);
        }
        exit;
    case 'logout':
        AuthController::logout();
        header('Location: ' . BASE_URL . 'login.php?logout=1');
        exit;
    case 'update_profile':
        require_role('client');
        $client_id = $_SESSION['user_id'];
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        if (!$name) {
            echo json_encode(['success'=>false, 'error'=>'Name is required.']);
            exit;
        }
        $result = ClientController::updateProfile($client_id, $name, $phone);
        if ($result) {
            // Get updated/sanitized name for feedback
            global $pdo;
            $stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
            $stmt->execute([$client_id]);
            $row = $stmt->fetch();
            $updatedName = $row ? $row['name'] : $name;
            $_SESSION['name'] = $updatedName;
            $_SESSION['phone'] = $phone;
            echo json_encode(['success'=>true, 'name'=>$updatedName]);
        } else {
            echo json_encode(['success'=>false, 'error'=>'Update failed.']);
        }
        exit;
    // Optionally: profile update...
}