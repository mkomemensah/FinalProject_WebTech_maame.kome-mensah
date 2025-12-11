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
    public static function getConsultantAppointments($consultant_id) {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT a.*, av.date AS date, av.start_time AS start_time, av.end_time AS end_time, cl.user_id AS client_user_id, cli.name AS client_name, cli.email AS client_email,
            f.consultant_notes, f.client_notes
             FROM appointments a
             JOIN users cli ON a.client_id = cli.user_id
             JOIN consultants co ON a.consultant_id = co.consultant_id
             JOIN availability av ON a.availability_id = av.availability_id
             JOIN users cl ON a.client_id = cl.user_id
             LEFT JOIN feedback f ON f.appointment_id = a.appointment_id
             WHERE a.consultant_id = ?
             ORDER BY av.date DESC, av.start_time DESC"
        );
        $stmt->execute([$consultant_id]);
        return $stmt->fetchAll();
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
        // Only allow if it is confirmed for this consultant
        $stmt = $pdo->prepare("SELECT a.*, av.date, av.end_time FROM appointments a JOIN availability av ON a.availability_id = av.availability_id WHERE a.appointment_id = ? AND a.consultant_id = ? AND a.status = 'confirmed'");
        $stmt->execute([$appointment_id, $consultant_id]);
        $appt = $stmt->fetch();
        if (!$appt) return ['success'=>false, 'error'=>'Appointment not found or not eligible.'];
        // (REMOVED time check) -- allow marking as completed at any time
        $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?")->execute([$appointment_id]);
        $admin_id = $_SESSION['user_id'] ?? null;
        if ($admin_id) write_audit($admin_id, 'mark_completed', 'appointment', $appointment_id, json_encode(['consultant_id'=>$consultant_id]));
        return ['success'=>true];
    }
}