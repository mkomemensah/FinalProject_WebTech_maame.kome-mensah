<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../app/controllers/AppointmentController.php';

class BookingTest extends TestCase {
    public function testCannotBookWithInvalidTime() {
        $data = [
            'consultant_id' => 99, // use id not in DB
            'date' => '2025-01-01',
            'time' => '99:99',
            'client_id' => 1
        ];
        $result = AppointmentController::book($data);
        $this->assertFalse($result['success']);
    }
}