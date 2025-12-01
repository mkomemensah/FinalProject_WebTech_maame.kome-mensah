<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../app/utils/security.php';

class SecurityTest extends TestCase {
    public function testPasswordHashAndVerify() {
        $pw = 'Secure!123';
        $hash = hash_password($pw);
        $this->assertTrue(verify_password($pw, $hash));
    }

    public function testXSSPrevention() {
        $input = '<script>alert("xss")</script>';
        $sanitized = sanitize_input($input);
        $this->assertStringNotContainsString('<script>', $sanitized);
    }
}