<?php
// This is where we handle all the user authentication stuff - logging in, registering, etc.
// It's like the bouncer at the club, but for our website

// Load up the tools we'll need
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/validators.php';
require_once __DIR__ . '/../utils/security.php';
require_once __DIR__ . '/../utils/session.php';

class AuthController {
    /**
     * Handles new user signups - both regular users and consultants
     * @param array $data All the user's info: name, email, password, phone, and role
     * @return array Empty if everything went well, or error messages if something's wrong
     */
    public static function register($data) {
        global $pdo;

        // First, let's check if everything looks good
        $errors = validate_registration($data);
        if (!empty($errors)) {
            return $errors; // Oops, something's not right
        }

        // Make sure this email isn't already taken
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([sanitize_input($data['email'])]);
        if ($stmt->rowCount() > 0) {
            return ['email' => 'Email already in use'];
        }

        // Hash that password! Never store it plain text
        $hashed_pw = hash_password($data['password']);

        // Only allow 'client' or 'consultant' roles, default to 'client'
        $role = in_array($data['role'], ['client','consultant']) ? $data['role'] : 'client';
        
        // Add the new user to the database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $result = $stmt->execute([
            sanitize_input($data['name']),    // Clean up the name
            sanitize_input($data['email']),   // Clean up the email
            $hashed_pw,                      // Already hashed, no need to sanitize
            sanitize_input($data['phone']),   // Clean up the phone number
            $role                            // Either 'client' or 'consultant'
        ]);
        
        if (!$result) {
            // Something went wrong with the database
            return ['general' => 'Registration failed, try again.'];
        }
        
        // If we got here, everything worked!
        return [];
    }

    /**
     * Handles user login - checks credentials and starts a secure session
     * @param array $data Just needs email and password
     * @return array Empty if login worked, or error messages if not
     */
    public static function login($data) {
        global $pdo;

        // First, make sure we have an email and password
        $errors = validate_login($data);
        if (!empty($errors)) {
            return $errors; // Tell them what they did wrong
        }

        // Look up the user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([sanitize_input($data['email'])]);
        $user = $stmt->fetch();

        // Check if the password matches (or if the user even exists)
        if (!$user || !verify_password($data['password'], $user['password'])) {
            // Don't tell them which one was wrong (email or password) for security
            return ['password' => 'Incorrect email or password.'];
        }
        
        // Make sure their account is active
        if ($user['status'] !== 'active') {
            return ['general' => 'Account is suspended or inactive.'];
        }

        // If we got here, the login is good! Set up their session
        secure_session_start();
        
        // Store what we need in the session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        
        // Security measure: regenerate the session ID to prevent session fixation
        session_regenerate_id(true);

        // No errors means success!
        return [];
    }

    /**
     * Logs the user out by nuking their session
     * It's like the digital equivalent of showing someone the door
     */
    public static function logout() {
        // Start the session if it's not already started
        secure_session_start();
        
        // Clear out all session data
        $_SESSION = [];
        
        // Destroy the session completely
        session_destroy();
    }
}