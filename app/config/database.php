<?php
// This file handles all the database connection stuff
// It's like the receptionist who knows how to talk to the database

// Check if we're running locally or on the school server
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    // Local development settings (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');      // Default XAMPP username
    define('DB_PASS', '');         // Default XAMPP password (empty)
    define('DB_NAME', 'consultease_db'); // Your local database name
    define('BASE_URL', 'http://localhost/FinalProject_WebTech_maame.kome-mensah/public/');
    define('APP_ENV', 'local');
} else {
    // School server settings
    define('DB_HOST', 'localhost');
    define('DB_USER', 'maame.kome-mensah');
    define('DB_PASS', 'purple300');
    define('DB_NAME', 'webtech_2025A_maame_kome-mensah');
    define('BASE_URL', 'http://169.239.251.102:341/~maame.kome-mensah/FinalProject_WebTech_maame.kome-mensah/public/');
    define('APP_ENV', 'production');
}

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set up MySQLi connection
$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    if (APP_ENV === 'local') {
        die("MySQLi Connection failed: " . $mysqli->connect_error);
    } else {
        error_log("MySQLi Connection failed: " . $mysqli->connect_error);
        die("We're having some technical difficulties. Please try again in a few minutes.");
    }
}

// Set up PDO connection
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    $error_message = date('[Y-m-d H:i:s] ') . 'Database Error: ' . $e->getMessage() . PHP_EOL;
    error_log($error_message, 3, __DIR__ . '/../../logs/error.log');
    if (APP_ENV === 'local') {
        die("PDO Connection failed: " . $e->getMessage());
    } else {
        die("We're having some technical difficulties. Please try again in a few minutes.");
    }
}