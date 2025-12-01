<?php
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$action = $_GET['action'] ?? '';
session_start();

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
    // Optionally: profile update...
}