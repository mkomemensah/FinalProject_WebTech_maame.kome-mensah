<?php
// app/middleware/role_middleware.php

require_once __DIR__ . '/../utils/session.php';
secure_session_start();
function require_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}