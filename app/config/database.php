<?php
// /app/config/database.php

// Detect environment (localhost or live server)
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    // Localhost (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'consultease_db');
    define('BASE_URL', 'http://localhost/FinalProject_WebTech_maame.kome-mensah/public/');
    define('APP_ENV', 'local');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    // Live school server
    define('DB_HOST', 'localhost');
    define('DB_USER', 'maame.kome-mensah');
    define('DB_PASS', 'purple300');
    define('DB_NAME', 'maame.kome-mensah_consultease');
    define('BASE_URL', 'http://169.239.251.102:341/~maame.kome-mensah/FinalProject_WebTech_maame.kome-mensah/public/');
    define('APP_ENV', 'production');
    ini_set('display_errors', 0);
}

// MySQLi connection
$mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$mysqli && APP_ENV === 'local') {
    die("MySQLi Connection failed: " . mysqli_connect_error());
}

// PDO connection
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => (APP_ENV === 'local') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        die("PDO Connection failed: " . $e->getMessage());
    } else {
        error_log(date('[Y-m-d H:i:s] ') . 'Database Error: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../../logs/error.log');
        die("A server error occurred. Please try again later.");
    }
}