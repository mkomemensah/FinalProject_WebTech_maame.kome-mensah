<?php
// This is the security guard for our application
// It makes sure only logged-in users can access certain pages

// First, load the session utilities and start a secure session
require_once __DIR__ . '/../utils/session.php';
secure_session_start();

// Check if the user is logged in by looking for their user_id in the session
// If they're not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    // Send them packing to the login page
    header("Location: " . BASE_URL . "login.php");
    // Make sure no code below this runs
    exit;
}

// If we get here, the user is logged in and can access the page
// The script that included this file will continue running as normal