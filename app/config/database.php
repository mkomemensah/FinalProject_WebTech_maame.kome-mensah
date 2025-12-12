<?php
// This file handles all the database connection stuff
// It's like the receptionist who knows how to talk to the database

// Figure out if we're running locally or on the live server
// This helps us use the right settings for each environment
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    // Local development settings (XAMPP)
    define('DB_HOST', 'localhost');      // Database is on the same machine
    define('DB_USER', 'root');          // Default XAMPP username
    define('DB_PASS', '');              // Default XAMPP password (empty)
    define('DB_NAME', 'consultease_db'); // Our local database name
    define('BASE_URL', 'http://localhost/FinalProject_WebTech_maame.kome-mensah/public/');
    define('APP_ENV', 'local');         // We're in development mode
    
    // Show all errors when we're developing locally
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    // Live server settings - be careful with these!
    define('DB_HOST', 'localhost');
    define('DB_USER', 'maame.kome-mensah');  // School server username
    define('DB_PASS', 'purple300');         // In a real app, this would be in an environment variable
    define('DB_NAME', 'maame.kome-mensah_consultease');
    define('BASE_URL', 'http://169.239.251.102:341/~maame.kome-mensah/FinalProject_WebTech_maame.kome-mensah/public/');
    define('APP_ENV', 'production');    // We're live, be careful!
    
    // Don't show errors to users in production
    ini_set('display_errors', 0);
}

// Set up MySQLi connection (some parts of the code might still use this)
$mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// If the connection fails and we're in development, show the error
if (!$mysqli && APP_ENV === 'local') {
    die("Oops! Couldn't connect to the database: " . mysqli_connect_error());
    // In production, this would be logged instead of shown to the user
}

// Set up PDO connection (this is the modern way to talk to databases in PHP)
try {
    // Create the connection string
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    
    // Set up PDO with some sensible defaults
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        // In development, throw exceptions when something goes wrong
        // In production, be more quiet about errors
        PDO::ATTR_ERRMODE => (APP_ENV === 'local') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
        
        // Always get results as associative arrays (no numeric indices)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
} catch (PDOException $e) {
    // If something goes wrong while connecting...
    if (APP_ENV === 'local') {
        // In development, show the actual error
        die("Couldn't connect to the database: " . $e->getMessage());
    } else {
        // In production, log the error and show a friendly message
        $error_message = date('[Y-m-d H:i:s] ') . 'Database Error: ' . $e->getMessage() . PHP_EOL;
        error_log($error_message, 3, __DIR__ . '/../../logs/error.log');
        die("We're having some technical difficulties. Please try again in a few minutes.");
    }
}