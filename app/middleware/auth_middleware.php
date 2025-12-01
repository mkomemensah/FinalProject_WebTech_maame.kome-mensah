<?php
// app/middleware/auth_middleware.php

require_once __DIR__ . '/../utils/session.php';
secure_session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}