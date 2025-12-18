<?php
// First things first, let's load up all the required files
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

// Start the session if it's not already running
secure_session_start();

// Check what action we're supposed to take from the request
$action = $_GET['action'] ?? '';

// Handle different appointment-related actions
switch ($action) {
    // When a client wants to book a new appointment
    case 'book':
        // Only logged-in clients can book appointments
        require_role('client');
        
        // Collect all the booking details from the form
        $data = [
            'consultant_id' => $_POST['consultant_id'] ?? null,  // Which consultant they're booking with
            'availability_id' => $_POST['availability_id'] ?? null,  // The specific time slot
            'client_id' => $_SESSION['user_id'],  // Who's making the booking
            'date' => $_POST['date'] ?? null,  // The day they want to meet
            'start_time' => $_POST['start_time'] ?? null,  // When it starts
            'end_time' => $_POST['end_time'] ?? null  // When it should end
        ];
        
        // Try to book the appointment and get the result
        $result = AppointmentController::book($data);
        
        // Send back a JSON response to the frontend
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    // When we need to fetch a list of appointments
    case 'list':
        try {
            // Different users see different appointment lists
            if ($_SESSION['role'] === 'client') {
                // Clients see their own upcoming appointments
                $appointments = AppointmentController::getClientAppointments($_SESSION['user_id']);
            } elseif ($_SESSION['role'] === 'consultant') {
                // For consultants, we need to auto-complete any past sessions first
                global $pdo;
                
                error_log("Consultant appointments API called. User ID: " . ($_SESSION['user_id'] ?? 'not set'));
                
                // Find out which consultant this user is
                $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $row = $stmt->fetch();
                $consultant_id = $row ? $row['consultant_id'] : null;
                
                error_log("Consultant lookup - user_id: " . $_SESSION['user_id'] . ", consultant_id: " . ($consultant_id ?? 'null'));
                
                // If we found the consultant, update any sessions that should be marked as complete
                if ($consultant_id) {
                    // Check how many appointments exist directly in database (all statuses)
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE consultant_id = ?");
                    $checkStmt->execute([$consultant_id]);
                    $totalCount = $checkStmt->fetchColumn();
                    error_log("Direct appointment count for consultant_id $consultant_id: $totalCount");
                    
                    // Check appointments by status
                    $statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE consultant_id = ? GROUP BY status");
                    $statusStmt->execute([$consultant_id]);
                    $statusCounts = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Appointments by status for consultant_id $consultant_id: " . json_encode($statusCounts));
                    
                    // Check if appointments have availability records
                    $availStmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments a LEFT JOIN availability av ON a.availability_id = av.availability_id WHERE a.consultant_id = ?");
                    $availStmt->execute([$consultant_id]);
                    $withAvail = $availStmt->fetchColumn();
                    error_log("Appointments with availability records: $withAvail");
                    
                    // Check appointments without availability
                    $noAvailStmt = $pdo->prepare("SELECT a.appointment_id, a.status, a.availability_id FROM appointments a LEFT JOIN availability av ON a.availability_id = av.availability_id WHERE a.consultant_id = ? AND av.availability_id IS NULL");
                    $noAvailStmt->execute([$consultant_id]);
                    $noAvail = $noAvailStmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($noAvail) > 0) {
                        error_log("Appointments WITHOUT availability records: " . json_encode($noAvail));
                    }
                    
                    AppointmentController::autoCompleteSessions($consultant_id);
                    $appointments = AppointmentController::getConsultantAppointments($consultant_id);
                    error_log("getConsultantAppointments returned " . count($appointments) . " appointments");
                    
                    // If we got 0 appointments but there are appointments in DB, something is wrong with the query
                    if (count($appointments) == 0 && $totalCount > 0) {
                        error_log("WARNING: Query returned 0 appointments but database has $totalCount appointments!");
                        
                        // Check what consultant_ids are actually in the appointments table
                        $checkConsultantStmt = $pdo->prepare("SELECT DISTINCT consultant_id, COUNT(*) as count FROM appointments GROUP BY consultant_id");
                        $checkConsultantStmt->execute();
                        $allConsultantIds = $checkConsultantStmt->fetchAll(PDO::FETCH_ASSOC);
                        error_log("All consultant_ids in appointments table: " . json_encode($allConsultantIds));
                        error_log("Looking for consultant_id: $consultant_id");
                        
                        // Try a simpler query to see what's wrong
                        $simpleStmt = $pdo->prepare("SELECT a.*, av.date, av.start_time, av.end_time FROM appointments a LEFT JOIN availability av ON a.availability_id = av.availability_id WHERE a.consultant_id = ? LIMIT 5");
                        $simpleStmt->execute([$consultant_id]);
                        $simpleResults = $simpleStmt->fetchAll(PDO::FETCH_ASSOC);
                        error_log("Simple query result (first 5): " . json_encode($simpleResults));
                        
                        // If simple query also returns 0, consultant_id definitely doesn't match
                        if (count($simpleResults) == 0) {
                            error_log("CRITICAL: Even simple query returned 0 - consultant_id mismatch confirmed!");
                            error_log("Consultant_id being queried: $consultant_id");
                            error_log("Consultant_ids in appointments: " . json_encode(array_column($allConsultantIds, 'consultant_id')));
                            
                            // Try to find appointments with ANY consultant_id to see if data exists
                            $anyStmt = $pdo->prepare("SELECT consultant_id, COUNT(*) as count FROM appointments GROUP BY consultant_id");
                            $anyStmt->execute();
                            $anyResults = $anyStmt->fetchAll(PDO::FETCH_ASSOC);
                            error_log("All appointments by consultant_id: " . json_encode($anyResults));
                        }
                    }
                } else {
                    error_log("No consultant_id found for user_id: " . $_SESSION['user_id']);
                    // Check if user exists in consultants table at all
                    $checkStmt = $pdo->prepare("SELECT * FROM consultants WHERE user_id = ?");
                    $checkStmt->execute([$_SESSION['user_id']]);
                    $allRows = $checkStmt->fetchAll();
                    error_log("Consultants table check for user_id " . $_SESSION['user_id'] . ": " . json_encode($allRows));
                    $appointments = [];
                }
            } elseif ($_SESSION['role'] === 'admin') {
                AppointmentController::autoCompleteSessions(); // global auto-mark
                $appointments = AppointmentController::getAllAppointments();
            } else {
                $appointments = [];
            }
            
            // Ensure appointments is always an array
            if (!is_array($appointments)) {
                error_log("Appointments is not an array: " . gettype($appointments));
                $appointments = [];
            }
            
            // Log final result before sending
            error_log("Final appointments count being sent: " . count($appointments));
            if (count($appointments) > 0) {
                error_log("First appointment in response: " . json_encode($appointments[0]));
            } else {
                // If we're a consultant and got 0 appointments, do a final check
                if ($_SESSION['role'] === 'consultant' && isset($consultant_id) && $consultant_id) {
                    // Quick check if appointments exist for this consultant_id
                    $quickCheck = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE consultant_id = ?");
                    $quickCheck->execute([$consultant_id]);
                    $quickCount = $quickCheck->fetchColumn();
                    
                    if ($quickCount > 0) {
                        error_log("CRITICAL ERROR: $quickCount appointments exist for consultant_id $consultant_id but query returned 0!");
                        error_log("This indicates a JOIN filtering issue or data mismatch.");
                        
                        // Try one more time with absolute minimal query
                        $absoluteMin = $pdo->prepare("SELECT appointment_id, status FROM appointments WHERE consultant_id = ? LIMIT 1");
                        $absoluteMin->execute([$consultant_id]);
                        $absResult = $absoluteMin->fetch();
                        if ($absResult) {
                            error_log("Absolute minimal query works - appointment exists: " . json_encode($absResult));
                            error_log("The issue is definitely with the JOINs in getConsultantAppointments()");
                        }
                    } else {
                        // Check if appointments exist with different consultant_id
                        $allAppts = $pdo->query("SELECT consultant_id, COUNT(*) as count FROM appointments GROUP BY consultant_id")->fetchAll(PDO::FETCH_ASSOC);
                        error_log("All appointments by consultant_id: " . json_encode($allAppts));
                        error_log("Your consultant_id: $consultant_id");
                    }
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode($appointments);
        } catch (Exception $e) {
            error_log("Error in appointments list API: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch appointments: ' . $e->getMessage()]);
        }
        break;
    // Consultant accepting an appointment
    case 'accept':
        require_role('consultant');
        $appointment_id = $_POST['appointment_id'];
        global $pdo;
        $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        $consultant_id = $row ? $row['consultant_id'] : null;
        $result = $consultant_id
            ? AppointmentController::acceptAppointment($appointment_id, $consultant_id)
            : ['success'=>false,'error'=>'Consultant not found'];
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
    // Consultant rejecting/cancelling an appointment
    case 'reject':
        require_role('consultant');
        $appointment_id = $_POST['appointment_id'];
        $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        $consultant_id = $row ? $row['consultant_id'] : null;
        $result = $consultant_id
            ? AppointmentController::rejectAppointment($appointment_id, $consultant_id)
            : ['success'=>false,'error'=>'Consultant not found'];
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
    case 'mark_completed':
        require_role('consultant');
        $appointment_id = $_POST['appointment_id'];
        $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        $consultant_id = $row ? $row['consultant_id'] : null;
        $result = $consultant_id
            ? AppointmentController::markCompleted($appointment_id, $consultant_id)
            : ['success'=>false,'error'=>'Consultant not found'];
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    case 'reschedule':
        require_role('client');
        
        $appointment_id = $_POST['appointment_id'] ?? null;
        $new_date = $_POST['new_date'] ?? null;
        $new_start_time = $_POST['new_start_time'] ?? null;
        $new_end_time = $_POST['new_end_time'] ?? null;
        
        if (!$appointment_id || !$new_date || !$new_start_time || !$new_end_time) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
            exit;
        }
        
        $result = AppointmentController::reschedule(
            $appointment_id,
            $_SESSION['user_id'],
            $new_date,
            $new_start_time,
            $new_end_time
        );
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    case 'cancel':
        $appointment_id = $_POST['appointment_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        if (!$appointment_id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Appointment ID is required.']);
            exit;
        }
        
        $result = AppointmentController::cancel(
            $appointment_id,
            $_SESSION['user_id'],
            $_SESSION['role'],
            $reason
        );
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
}