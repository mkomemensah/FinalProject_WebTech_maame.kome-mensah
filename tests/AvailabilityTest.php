<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../app/controllers/ConsultantController.php';

class AvailabilityTest extends TestCase {
    public function testCannotSetAvailabilityWithEndTimeBeforeStartTime() {
        $data = [
            'consultant_id' => 1,
            'date' => '2025-01-01',
            'start_time' => '16:00',
            'end_time' => '15:00'
        ];
        $result = ConsultantController::addAvailability($data);
        $this->assertFalse($result['success']);
    }
}