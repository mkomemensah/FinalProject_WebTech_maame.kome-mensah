<?php
// /app/controllers/AuthController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/validators.php';
require_once __DIR__ . '/../utils/security.php';
require_once __DIR__ . '/../utils/session.php';

class AuthController {
    /**
     * Register a new user (Client or Consultant)
     * @param array $data  [name, email, password, phone, role]
     * @return array $errors   (empty on success)
     */
    public static function register($data) {
        global $pdo;

        // 1. Validate input data
        $errors = validate_registration($data);
        if (!empty($errors)) {
            return $errors;
        }

        // 2. Check for duplicate email
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([sanitize_input($data['email'])]);
        if ($stmt->rowCount() > 0) {
            return ['email' => 'Email already in use'];
        }

        // 3. Hash the password
        $hashed_pw = hash_password($data['password']);

        // 4. Insert user (only valid roles allowed)
        $role = in_array($data['role'], ['client','consultant']) ? $data['role'] : 'client';
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $result = $stmt->execute([
            sanitize_input($data['name']),
            sanitize_input($data['email']),
            $hashed_pw,
            sanitize_input($data['phone']),
            $role
        ]);
        if (!$result) {
            return ['general' => 'Registration failed, try again.'];
        }
        return [];
    }

    /**
     * Log in a user by email/password.
     * Sets session: user_id, role, name
     * @param array $data [email, password]
     * @return array $errors (empty on success)
     */
    public static function login($data) {
        global $pdo;

        // 1. Validate input
        $errors = validate_login($data);
        if (!empty($errors)) {
            return $errors;
        }

        // 2. Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([sanitize_input($data['email'])]);
        $user = $stmt->fetch();

        if (!$user || !verify_password($data['password'], $user['password'])) {
            return ['password' => 'Incorrect email or password.'];
        }
        if ($user['status'] !== 'active') {
            return ['general' => 'Account is suspended or inactive.'];
        }

        // 3. Security: session management
        secure_session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        session_regenerate_id(true);

        return [];
    }

    /**
     * Log out the user and destroy the session.
     */
    public static function logout() {
        secure_session_start();
        $_SESSION = [];
        session_destroy();
    }
}