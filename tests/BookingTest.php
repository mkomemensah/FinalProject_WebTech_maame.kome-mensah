<?php
require_once __DIR__ . '/../app/controllers/AppointmentController.php';

class BookingTest {

    public function testCannotBookWithInvalidTime() {
        $data = [
            'consultant_id' => 99,
            'date' => '2025-01-01',
            'time' => '99:99',
            'client_id' => 1
        ];

        $result = AppointmentController::book($data);
        assertFalse($result['success'], "Booking with invalid time should fail");
    }

    public function testCanBookValidAppointment() {
        $data = [
            'consultant_id' => 1,
            'date' => '2025-01-01',
            'time' => '10:00',
            'client_id' => 1
        ];

        $result = AppointmentController::book($data);
        assertTrue($result['success'], "Valid booking should pass");
    }
}
