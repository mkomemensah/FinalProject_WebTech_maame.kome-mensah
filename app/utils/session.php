<?php
// This file handles all the session-related security stuff
// It's like the bouncer for user sessions - keeps the bad guys out

/**
 * Starts a secure session with all the right security headers
 * This is like giving each user a special ID badge when they log in
 */
function secure_session_start() {
    // Only start a session if one isn't already running
    if(session_status() === PHP_SESSION_NONE) {
        // Configure session cookies to be super secure
        session_start([
            'cookie_httponly' => true,    // Can't access cookie via JavaScript
            'cookie_secure' => isset($_SERVER['HTTPS']),  // Only send over HTTPS
            'cookie_samesite' => 'Strict'  // Prevent CSRF attacks
        ]);
    }
    
    // Check if the user has been inactive for too long (15 minutes)
    $inactive_timeout = 15 * 60; // 15 minutes in seconds
    if(isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > $inactive_timeout) {
        // If they've been idle too long, kick them out
        session_unset();     // Clear all session variables
        session_destroy();   // Nuke the session
        
        // Optional: Redirect to login page with a message
        // header('Location: /login.php?timeout=1');
        // exit();
    }
    
    // Update the last activity time to now
    $_SESSION['LAST_ACTIVITY'] = time();
}