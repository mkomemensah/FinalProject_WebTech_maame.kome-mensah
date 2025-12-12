<?php
require_once __DIR__ . '/../app/controllers/ConsultantController.php';

class AvailabilityTest {

    public function testCannotSetAvailabilityWithEndTimeBeforeStartTime() {
        $data = [
            'consultant_id' => 1,
            'date' => '2025-01-01',
            'start_time' => '16:00',
            'end_time' => '15:00'
        ];

        $result = ConsultantController::addAvailability($data);
        assertFalse($result['success'], "End time before start time should fail");
    }

    public function testCanSetValidAvailability() {
        $data = [
            'consultant_id' => 1,
            'date' => '2025-01-01',
            'start_time' => '10:00',
            'end_time' => '12:00'
        ];

        $result = ConsultantController::addAvailability($data);
        assertTrue($result['success'], "Valid availability should pass");
    }
}
