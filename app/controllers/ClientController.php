<?php
// /app/controllers/ClientController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/validators.php';
require_once __DIR__ . '/../utils/security.php';

class ClientController
{
    /**
     * Get all approved consultants (to show to clients).
     * @return array
     */
    public static function getApprovedConsultants()
    {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT u.user_id, u.name, u.email, c.consultant_id, c.bio, c.years_of_experience, c.profile_status, e.name AS expertise
             FROM consultants c
             JOIN users u ON c.user_id = u.user_id
             LEFT JOIN expertise e ON c.expertise_id = e.expertise_id
             WHERE c.profile_status = 'approved' AND u.status = 'active'"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all appointments for a client (current and past).
     * @param int $client_id
     * @return array
     */
    public static function getAppointments($client_id)
    {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT a.*, 
                    c.consultant_id, u.name AS consultant_name, av.date, av.start_time, av.end_time 
            FROM appointments a
            JOIN consultants c ON a.consultant_id = c.consultant_id
            JOIN users u ON c.user_id = u.user_id
            JOIN availability av ON a.availability_id = av.availability_id
            WHERE a.client_id = ?
            ORDER BY av.date DESC, av.start_time DESC"
        );
        $stmt->execute([$client_id]);
        return $stmt->fetchAll();
    }

    /**
     * Update client profile (name/phone)
     * @param int $client_id
     * @param string $name
     * @param string $phone
     * @return bool
     */
    public static function updateProfile($client_id, $name, $phone)
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ? AND role = 'client'");
        return $stmt->execute([sanitize_input($name), sanitize_input($phone), $client_id]);
    }

    /**
     * Submit a business problem for an appointment.
     * @param int $appointment_id
     * @param string $description
     * @return bool
     */
    public static function submitProblem($appointment_id, $description)
    {
        global $pdo;
        $stmt = $pdo->prepare(
            "INSERT INTO business_problems (appointment_id, description) VALUES (?, ?)"
        );
        return $stmt->execute([$appointment_id, sanitize_input($description)]);
    }
}