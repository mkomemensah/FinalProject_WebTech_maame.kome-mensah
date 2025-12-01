<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/controllers/AuthController.php';

class AuthTest extends TestCase {
    public function testRegisterRejectsWeakPassword() {
        $errors = AuthController::register([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0500000000',
            'password' => 'weak',
            'role' => 'client'
        ]);
        $this->assertArrayHasKey('password', $errors);
    }

    // Add more: valid registration, login success/failure, etc.
}