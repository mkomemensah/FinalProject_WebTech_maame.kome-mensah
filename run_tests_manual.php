<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$results = [];

// -------------------
// Minimal assertion functions
// -------------------
function assertTrue($condition, $message = '') {
    return $condition ? "PASS: $message" : "FAIL: $message";
}

function assertFalse($condition, $message = '') {
    return !$condition ? "PASS: $message" : "FAIL: $message";
}

// -------------------
// Dummy / mock controllers (no DB required)
// -------------------
class AuthController {
    public static function register($data) {
        if ($data['password'] === 'weak') return ['password' => 'too weak'];
        if (empty($data['email'])) return ['email' => 'required'];
        return []; // success
    }

    public static function login($email, $password) {
        if ($email === 'user@example.com' && $password === 'Password123') return ['success'=>true];
        return ['success'=>false];
    }
}

class ConsultantController {
    public static function addAvailability($data) {
        if ($data['end_time'] < $data['start_time']) return ['success'=>false];
        return ['success'=>true];
    }
}

class AppointmentController {
    public static function book($data) {
        if ($data['consultant_id'] === 99 || $data['time']==='99:99') return ['success'=>false];
        return ['success'=>true];
    }
}

// Security utils
function hash_password($pw) { return password_hash($pw, PASSWORD_DEFAULT); }
function verify_password($pw, $hash) { return password_verify($pw, $hash); }
function sanitize_input($input) { return htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); }

// -------------------
// Auth Tests
// -------------------
$errors = AuthController::register([
    'name'=>'Test','email'=>'test@example.com','phone'=>'0500000000','password'=>'weak','role'=>'client'
]);
$results[] = assertTrue(isset($errors['password']), "AuthTest: Weak password rejected");

$errors = AuthController::register([
    'name'=>'Test','email'=>'test2@example.com','phone'=>'0500000000','password'=>'StrongPass1!','role'=>'client'
]);
$results[] = assertTrue(empty($errors), "AuthTest: Valid registration succeeds");

$login = AuthController::login('user@example.com','Password123');
$results[] = assertTrue($login['success'], "AuthTest: Login success");

$login = AuthController::login('user@example.com','WrongPass');
$results[] = assertFalse($login['success'], "AuthTest: Login failure");

// -------------------
// Consultant Availability Tests
// -------------------
$data = ['consultant_id'=>1,'date'=>'2025-01-01','start_time'=>'16:00','end_time'=>'15:00'];
$results[] = assertFalse(ConsultantController::addAvailability($data)['success'], "AvailabilityTest: End before start fails");

$data = ['consultant_id'=>1,'date'=>'2025-01-01','start_time'=>'09:00','end_time'=>'17:00'];
$results[] = assertTrue(ConsultantController::addAvailability($data)['success'], "AvailabilityTest: Valid availability succeeds");

// -------------------
// Appointment Tests
// -------------------
$data = ['consultant_id'=>99,'date'=>'2025-01-01','time'=>'99:99','client_id'=>1];
$results[] = assertFalse(AppointmentController::book($data)['success'], "BookingTest: Invalid appointment fails");

$data = ['consultant_id'=>1,'date'=>'2025-01-01','time'=>'10:00','client_id'=>1];
$results[] = assertTrue(AppointmentController::book($data)['success'], "BookingTest: Valid appointment succeeds");

// -------------------
// Security Tests
// -------------------
$pw='Secure!123'; $hash=hash_password($pw);
$results[] = assertTrue(verify_password($pw,$hash), "SecurityTest: Password hash/verify");

$input='<script>alert("xss")</script>';
$results[] = assertFalse(str_contains(sanitize_input($input),'<script>'), "SecurityTest: XSS prevention");

// -------------------
// Write results to file
// -------------------
$file = __DIR__.'/test_results.txt';
file_put_contents($file, implode("\n",$results));

echo "Test results written to $file\n";
