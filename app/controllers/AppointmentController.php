<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/validators.php';
require_once __DIR__ . '/../utils/audit.php';

class AppointmentController {
    // Book appointment (updated: real slot, no double bookings)
    public static function book($data) {
        global $pdo;
        date_default_timezone_set('Africa/Accra');
        // Support new-mode: consultant_id, client_id, date, start_time, end_time
        if (!empty($data['date']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            $today = date('Y-m-d');
            if ($data['date'] < $today) {
                return ['success' => false, 'error' => 'Cannot book a date before today.'];
            }
            if ($data['date'] == $today) {
                $nowH = date('H'); $nowM = date('i');
                $nowTime = $nowH.':'.$nowM;
                if ($data['start_time'] <= $nowTime) {
                    return ['success' => false, 'error' => 'Pick a start time later than now.'];
                }
            }
            $slotDate = $data['date'] . ' ' . $data['start_time'];
            if (strtotime($slotDate) < time()) {
                return ['success' => false, 'error' => 'Must pick a future date/time.'];
            }
            if ($data['end_time'] <= $data['start_time']) {
                return ['success' => false, 'error' => 'End time must be after start.'];
            }
            // 2. Prevent double booking for consultant at this time
            $q = "SELECT av.availability_id
                  FROM availability av
                  JOIN appointments a ON av.availability_id = a.availability_id
                  WHERE av.consultant_id=? AND av.date=? AND av.start_time=? AND av.end_time=? 
                  AND a.status IN ('pending','confirmed')";
            $stmt = $pdo->prepare($q);
            $stmt->execute([$data['consultant_id'],$data['date'],$data['start_time'],$data['end_time']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'This slot is already booked.'];
            }
            // 3. Create availability row for this date/time
            $stmt = $pdo->prepare("INSERT INTO availability (consultant_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, 'booked')");
            $stmt->execute([$data['consultant_id'],$data['date'],$data['start_time'],$data['end_time']]);
            $availability_id = $pdo->lastInsertId();

            // 4. Insert appointment
            $stmt = $pdo->prepare("INSERT INTO appointments (client_id, consultant_id, availability_id, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$data['client_id'], $data['consultant_id'], $availability_id]);
            return ['success'=>true];
        }
        // Old method: fixed slot/availability
        if (empty($data['client_id']) || empty($data['consultant_id']) || empty($data['availability_id'])) {
            return ['success' => false, 'error' => 'Missing required data.'];
        }
        // 1. Check consultant owns slot
        $stmt = $pdo->prepare("SELECT * FROM availability WHERE availability_id = ? AND consultant_id = ? AND status = 'available'");
        $stmt->execute([$data['availability_id'], $data['consultant_id']]);
        $slot = $stmt->fetch();
        if (!$slot) {
            return ['success' => false, 'error' => 'Selected slot unavailable.'];
        }
        // 2. Prevent double bookings (any appointment for this slot pending or confirmed)
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE availability_id = ? AND status IN ('pending','confirmed')");
        $stmt->execute([$data['availability_id']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Slot already booked.'];
        }
        // 3. Insert new appointment (pending)
        $stmt = $pdo->prepare("INSERT INTO appointments (client_id, consultant_id, availability_id, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->execute([$data['client_id'], $data['consultant_id'], $data['availability_id']]);
        // 4. Update availability (optional: mark as booked immediately)
        $pdo->prepare("UPDATE availability SET status = 'booked' WHERE availability_id = ?")->execute([$data['availability_id']]);
        return ['success' => true];
    }
    public static function getClientAppointments($client_id) {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT a.*, 
                av.date AS date, av.start_time AS start_time, av.end_time AS end_time, 
                co.consultant_id, 
                u.name AS consultant_name, u.email AS consultant_email, 
                f.consultant_notes, f.client_notes
             FROM appointments a
             JOIN consultants co ON a.consultant_id = co.consultant_id
             JOIN users u ON co.user_id = u.user_id
             JOIN availability av ON a.availability_id = av.availability_id
             LEFT JOIN feedback f ON f.appointment_id = a.appointment_id
             WHERE a.client_id = ?
             ORDER BY av.date DESC, av.start_time DESC"
        );
        $stmt->execute([$client_id]);
        return $stmt->fetchAll();
    }
    /**
     * Reschedule an existing appointment
     * @param int $appointment_id
     * @param int $client_id
     * @param string $new_date
     * @param string $new_start_time
     * @param string $new_end_time
     * @return array
     */
    public static function reschedule($appointment_id, $client_id, $new_date, $new_start_time, $new_end_time) {
        global $pdo;
        
        try {
            $pdo->beginTransaction();
            
            // 1. Verify the appointment exists and belongs to the client
            $stmt = $pdo->prepare(
                "SELECT a.*, av.date, av.start_time, av.end_time, a.consultant_id 
                 FROM appointments a 
                 JOIN availability av ON a.availability_id = av.availability_id 
                 WHERE a.appointment_id = ? AND a.client_id = ? AND a.status IN ('pending', 'confirmed')"
            );
            $stmt->execute([$appointment_id, $client_id]);
            $appointment = $stmt->fetch();
            
            if (!$appointment) {
                return ['success' => false, 'error' => 'Appointment not found or cannot be rescheduled.'];
            }
            
            // 2. Validate new date/time
            $now = new DateTime();
            $newDateTime = new DateTime("$new_date $new_start_time");
            
            if ($newDateTime <= $now) {
                return ['success' => false, 'error' => 'New appointment time must be in the future.'];
            }
            
            if ($new_start_time >= $new_end_time) {
                return ['success' => false, 'error' => 'End time must be after start time.'];
            }
            
            // 3. Check if the consultant is available at the new time
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) as conflict_count 
                 FROM availability av 
                 JOIN appointments a ON av.availability_id = a.availability_id 
                 WHERE av.consultant_id = ? 
                 AND av.date = ? 
                 AND av.start_time = ? 
                 AND av.end_time = ? 
                 AND a.appointment_id != ? 
                 AND a.status IN ('pending', 'confirmed')"
            );
            $stmt->execute([
                $appointment['consultant_id'], 
                $new_date, 
                $new_start_time, 
                $new_end_time, 
                $appointment_id
            ]);
            $conflict = $stmt->fetch();
            
            if ($conflict['conflict_count'] > 0) {
                return ['success' => false, 'error' => 'The selected time slot is not available.'];
            }
            
            // 4. Create new availability for the rescheduled time
            $stmt = $pdo->prepare(
                "INSERT INTO availability (consultant_id, date, start_time, end_time, status) 
                 VALUES (?, ?, ?, ?, 'booked')"
            );
            $stmt->execute([
                $appointment['consultant_id'],
                $new_date,
                $new_start_time,
                $new_end_time
            ]);
            $new_availability_id = $pdo->lastInsertId();
            
            // 5. Update the appointment with the new availability
            $stmt = $pdo->prepare(
                "UPDATE appointments 
                 SET availability_id = ?, status = 'pending', updated_at = NOW() 
                 WHERE appointment_id = ?"
            );
            $stmt->execute([$new_availability_id, $appointment_id]);
            
            // 6. Log the reschedule in audit log
            $audit_data = [
                'old_date' => $appointment['date'],
                'old_start_time' => $appointment['start_time'],
                'old_end_time' => $appointment['end_time'],
                'new_date' => $new_date,
                'new_start_time' => $new_start_time,
                'new_end_time' => $new_end_time
            ];
            
            Audit::log(
                $client_id,
                'appointment_rescheduled',
                'appointment',
                $appointment_id,
                json_encode($audit_data)
            );
            
            $pdo->commit();
            return ['success' => true, 'message' => 'Appointment rescheduled successfully.'];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error rescheduling appointment: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to reschedule appointment. Please try again.'];
        }
    }
    
    /**
     * Cancel an appointment
     * @param int $appointment_id
     * @param int $user_id
     * @param string $user_role
     * @param string $reason
     * @return array
     */
    public static function cancel($appointment_id, $user_id, $user_role, $reason = '') {
        global $pdo;
        
        try {
            $pdo->beginTransaction();
            
            // 1. Get the appointment with related data
            $query = "SELECT a.*, av.date, av.start_time, av.end_time, c.user_id as client_user_id, co.user_id as consultant_user_id 
                     FROM appointments a
                     JOIN availability av ON a.availability_id = av.availability_id
                     JOIN users c ON a.client_id = c.user_id
                     JOIN consultants co ON a.consultant_id = co.consultant_id
                     WHERE a.appointment_id = ?";
            
            $params = [$appointment_id];
            
            // Add role-based access control
            if ($user_role === 'client') {
                $query .= " AND a.client_id = ?";
                $params[] = $user_id;
            } elseif ($user_role === 'consultant') {
                $query .= " AND co.user_id = ?";
                $params[] = $user_id;
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $appointment = $stmt->fetch();
            
            if (!$appointment) {
                return ['success' => false, 'error' => 'Appointment not found or you do not have permission to cancel it.'];
            }
            
            // 2. Check if the appointment can be cancelled
            $appointment_time = new DateTime("{$appointment['date']} {$appointment['start_time']}");
            $now = new DateTime();
            $hours_until_appointment = ($appointment_time->getTimestamp() - $now->getTimestamp()) / 3600;
            
            // Allow cancellation up to 1 hour before the appointment
            if ($hours_until_appointment < 1 && $user_role === 'client') {
                return ['success' => false, 'error' => 'Appointments can only be cancelled at least 1 hour in advance. Please contact support.'];
            }
            
            // 3. Update the appointment status to cancelled
            $stmt = $pdo->prepare(
                "UPDATE appointments 
                 SET status = 'cancelled', 
                     cancelled_at = NOW(), 
                     cancelled_by = ?,
                     cancellation_reason = ?
                 WHERE appointment_id = ?"
            );
            $stmt->execute([$user_id, $reason, $appointment_id]);
            
            // 4. Update the availability status back to available
            $stmt = $pdo->prepare(
                "UPDATE availability 
                 SET status = 'available' 
                 WHERE availability_id = ?"
            );
            $stmt->execute([$appointment['availability_id']]);
            
            // 5. Log the cancellation
            $audit_data = [
                'cancelled_by' => $user_id,
                'user_role' => $user_role,
                'reason' => $reason,
                'appointment_time' => "{$appointment['date']} {$appointment['start_time']}"
            ];
            
            Audit::log(
                $user_id,
                'appointment_cancelled',
                'appointment',
                $appointment_id,
                json_encode($audit_data)
            );
            
            $pdo->commit();
            return ['success' => true, 'message' => 'Appointment cancelled successfully.'];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error cancelling appointment: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to cancel appointment. Please try again.'];
        }
    }
    
    public static function getConsultantAppointments($consultant_id) {
        global $pdo;
        error_log("Getting appointments for consultant ID: " . $consultant_id);
        
        try {
            // First, check if consultant_id is valid
            if (empty($consultant_id)) {
                error_log("Empty consultant_id provided");
                return [];
            }
            
            // First, check if appointments exist at all for this consultant
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE consultant_id = ?");
            $checkStmt->execute([$consultant_id]);
            $totalAppointments = $checkStmt->fetchColumn();
            error_log("Total appointments in database for consultant_id $consultant_id: $totalAppointments");
            
            if ($totalAppointments > 0) {
                // Check if availability records exist for these appointments
                $checkAvailStmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments a JOIN availability av ON a.availability_id = av.availability_id WHERE a.consultant_id = ?");
                $checkAvailStmt->execute([$consultant_id]);
                $totalWithAvail = $checkAvailStmt->fetchColumn();
                error_log("Appointments with availability records: $totalWithAvail");
            }
            
            // Use COALESCE to handle potentially missing client_notes column gracefully
            // This query returns ALL appointments regardless of status (pending, confirmed, completed, cancelled)
            $sql = "SELECT a.*, 
                           av.date AS date, 
                           av.start_time AS start_time, 
                           av.end_time AS end_time, 
                           u.user_id AS client_user_id, 
                           u.name AS client_name, 
                           u.email AS client_email,
                           COALESCE(f.consultant_notes, '') AS consultant_notes, 
                           COALESCE(f.client_notes, '') AS client_notes, 
                           COALESCE(bp.description, '') AS business_problem
                    FROM appointments a
                    INNER JOIN availability av ON a.availability_id = av.availability_id
                    INNER JOIN users u ON a.client_id = u.user_id
                    LEFT JOIN feedback f ON f.appointment_id = a.appointment_id
                    LEFT JOIN business_problems bp ON bp.appointment_id = a.appointment_id
                    WHERE a.consultant_id = ?
                    ORDER BY 
                      CASE a.status 
                        WHEN 'pending' THEN 1 
                        WHEN 'confirmed' THEN 2 
                        WHEN 'completed' THEN 3 
                        WHEN 'cancelled' THEN 4 
                        ELSE 5 
                      END,
                      av.date DESC, 
                      av.start_time DESC";
            
            error_log("Executing SQL for consultant_id: " . $consultant_id);
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$consultant_id]);
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($result) . " appointments for consultant ID: " . $consultant_id);
            
            // Log status breakdown
            $statusBreakdown = [];
            foreach ($result as $apt) {
                $status = $apt['status'] ?? 'unknown';
                $statusBreakdown[$status] = ($statusBreakdown[$status] ?? 0) + 1;
            }
            error_log("Status breakdown: " . json_encode($statusBreakdown));
            
            // Log the first appointment if any
            if (count($result) > 0) {
                error_log("First appointment data: " . json_encode($result[0]));
            } else {
                error_log("No appointments found for consultant ID: " . $consultant_id);
                // Double-check if appointments exist for this consultant
                $checkStmt = $pdo->prepare("SELECT COUNT(*) as total, status FROM appointments WHERE consultant_id = ? GROUP BY status");
                $checkStmt->execute([$consultant_id]);
                $checkResults = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Direct appointment count by status: " . json_encode($checkResults));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in getConsultantAppointments: " . $e->getMessage());
            error_log("SQL Error Info: " . print_r($pdo->errorInfo(), true));
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        } catch (Exception $e) {
            error_log("Error in getConsultantAppointments: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }
    public static function getAllAppointments() {
        global $pdo;
        $sql = "SELECT a.*, av.date AS date, av.start_time AS start_time, av.end_time AS end_time,
            c.consultant_id, cu.name AS consultant_name, cu.email AS consultant_email,
            cl.user_id AS client_user_id, cl.name AS client_name, cl.email AS client_email,
            f.consultant_notes, f.client_notes, bp.description AS problem_description
            FROM appointments a
            JOIN availability av ON a.availability_id = av.availability_id
            JOIN consultants c ON a.consultant_id = c.consultant_id
            JOIN users cu ON c.user_id = cu.user_id
            JOIN users cl ON a.client_id = cl.user_id
            LEFT JOIN feedback f ON f.appointment_id = a.appointment_id
            LEFT JOIN business_problems bp ON bp.appointment_id = a.appointment_id
            ORDER BY av.date DESC, av.start_time DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function submitProblem($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO business_problems (appointment_id, description) VALUES (?, ?)");
        $stmt->execute([$data['appointment_id'], $data['description']]);
        return ['success'=>true];
    }
    public static function submitFeedback($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO feedback (appointment_id, consultant_notes) VALUES (?, ?)");
        $stmt->execute([$data['appointment_id'], $data['consultant_notes']]);
        return ['success'=>true];
    }
    public static function submitClientFeedback($data) {
        global $pdo;
        // Insert or update feedback for the appointment (allow only one row per appointment ID)
        $stmt = $pdo->prepare("SELECT * FROM feedback WHERE appointment_id = ?");
        $stmt->execute([$data['appointment_id']]);
        if ($row = $stmt->fetch()) {
            // Update only client_notes
            $stmt = $pdo->prepare("UPDATE feedback SET client_notes = ? WHERE appointment_id = ?");
            $stmt->execute([$data['client_notes'], $data['appointment_id']]);
            return ['success' => true];
        } else {
            // Insert new feedback row
            $stmt = $pdo->prepare("INSERT INTO feedback (appointment_id, client_notes) VALUES (?, ?)");
            $stmt->execute([$data['appointment_id'], $data['client_notes']]);
            return ['success'=>true];
        }
    }
    // Accept appointment (consultant side)
    public static function acceptAppointment($appointment_id, $consultant_id) {
        global $pdo;
        // Check appointment exists and belongs to consultant, is pending
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND consultant_id = ? AND status = 'pending'");
        $stmt->execute([$appointment_id, $consultant_id]);
        $appt = $stmt->fetch();
        if (!$appt) {
            return ['success' => false, 'error' => 'Appointment not found or already handled.'];
        }
        // Double-check no other accepted booking for slot
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE availability_id = ? AND status = 'confirmed'");
        $stmt->execute([$appt['availability_id']]);
        if ($stmt->fetch()) {
            // There is already a confirmed booking!
            // Optionally, you could auto-reject or inform the client here
            return ['success' => false, 'error' => 'Slot already confirmed for another appointment.'];
        }
        // Set appointment status to confirmed
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        // Set slot to booked (redundant but safe)
        $pdo->prepare("UPDATE availability SET status = 'booked' WHERE availability_id = ?")->execute([$appt['availability_id']]);
        // Audit (if performed by admin, record it)
        $admin_id = $_SESSION['user_id'] ?? null;
        if ($admin_id) write_audit($admin_id, 'confirm_appointment', 'appointment', $appointment_id, json_encode(['consultant_id'=>$consultant_id]));
        return ['success'=>true];
    }

    // Reject/cancel appointment (consultant side)
    public static function rejectAppointment($appointment_id, $consultant_id) {
        global $pdo;
        // Check appointment exists and belongs to consultant, is pending
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND consultant_id = ? AND status = 'pending'");
        $stmt->execute([$appointment_id, $consultant_id]);
        $appt = $stmt->fetch();
        if (!$appt) {
            return ['success' => false, 'error' => 'Appointment not found or already handled.'];
        }
        // Set appointment status to cancelled
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        // Mark slot as available again
        $pdo->prepare("UPDATE availability SET status = 'available' WHERE availability_id = ?")->execute([$appt['availability_id']]);
        $admin_id = $_SESSION['user_id'] ?? null;
        if ($admin_id) write_audit($admin_id, 'cancel_appointment', 'appointment', $appointment_id, json_encode(['consultant_id'=>$consultant_id]));
        return ['success'=>true];
    }

    public static function autoCompleteSessions($consultant_id=null) {
        global $pdo;
        $sql = "SELECT a.appointment_id, av.date, av.end_time FROM appointments a JOIN availability av ON a.availability_id = av.availability_id WHERE a.status = 'confirmed'";
        $params = [];
        if ($consultant_id) {
            $sql .= " AND a.consultant_id = ?"; $params[] = $consultant_id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $now = date('Y-m-d H:i:s');
        while ($row = $stmt->fetch()) {
            $endDateTime = $row['date'] . ' ' . $row['end_time'];
            if (strtotime($endDateTime) < strtotime($now)) {
                $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?")->execute([$row['appointment_id']]);
            }
        }
    }

    public static function markCompleted($appointment_id, $consultant_id) {
        global $pdo;
        error_log("markCompleted called: appointment_id=$appointment_id, consultant_id=$consultant_id");
        
        // Only allow if it is confirmed for this consultant
        $stmt = $pdo->prepare("SELECT a.*, av.date, av.end_time FROM appointments a JOIN availability av ON a.availability_id = av.availability_id WHERE a.appointment_id = ? AND a.consultant_id = ? AND a.status = 'confirmed'");
        $stmt->execute([$appointment_id, $consultant_id]);
        $appt = $stmt->fetch();
        
        if (!$appt) {
            error_log("Appointment not found or not eligible. appointment_id=$appointment_id, consultant_id=$consultant_id");
            // Check what the actual status is
            $checkStmt = $pdo->prepare("SELECT appointment_id, consultant_id, status FROM appointments WHERE appointment_id = ?");
            $checkStmt->execute([$appointment_id]);
            $checkAppt = $checkStmt->fetch();
            if ($checkAppt) {
                error_log("Appointment exists but status is: " . $checkAppt['status'] . ", consultant_id match: " . ($checkAppt['consultant_id'] == $consultant_id ? 'yes' : 'no'));
            } else {
                error_log("Appointment does not exist in database");
            }
            return ['success'=>false, 'error'=>'Appointment not found or not eligible.'];
        }
        
        // (REMOVED time check) -- allow marking as completed at any time
        $updateStmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
        $updateStmt->execute([$appointment_id]);
        $rowsAffected = $updateStmt->rowCount();
        error_log("Updated appointment $appointment_id to completed. Rows affected: $rowsAffected");
        
        $admin_id = $_SESSION['user_id'] ?? null;
        if ($admin_id) write_audit($admin_id, 'mark_completed', 'appointment', $appointment_id, json_encode(['consultant_id'=>$consultant_id]));
        return ['success'=>true];
    }
}