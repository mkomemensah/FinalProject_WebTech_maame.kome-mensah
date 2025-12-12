<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

class AuthTest {

    public function testRegisterRejectsWeakPassword() {
        $errors = AuthController::register([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0500000000',
            'password' => 'weak',
            'role' => 'client'
        ]);

        // Check that 'password' key exists in errors
        assertTrue(isset($errors['password']), "Weak password should be rejected");
    }

    public function testRegisterAcceptsStrongPassword() {
        $errors = AuthController::register([
            'name' => 'Strong User',
            'email' => 'strong@example.com',
            'phone' => '0500000001',
            'password' => 'Strong!1234',
            'role' => 'client'
        ]);

        assertTrue(empty($errors), "Strong password should pass registration");
    }
}
