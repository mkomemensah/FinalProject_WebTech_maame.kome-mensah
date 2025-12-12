<?php
require_once __DIR__ . '/../app/utils/security.php';

class SecurityTest {

    public function testPasswordHashAndVerify() {
        $pw = 'Secure!123';
        $hash = hash_password($pw);
        assertTrue(verify_password($pw, $hash), "Password should verify correctly");
    }

    public function testXSSPrevention() {
        $input = '<script>alert("xss")</script>';
        $sanitized = sanitize_input($input);
        assertFalse(str_contains($sanitized, '<script>'), "Input should be sanitized to prevent XSS");
    }
}
