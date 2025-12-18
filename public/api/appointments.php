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
                
                // Find out which consultant this user is
                $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $row = $stmt->fetch();
                $consultant_id = $row ? $row['consultant_id'] : null;
                
                // If we found the consultant, update any sessions that should be marked as complete
                if ($consultant_id) {
                    AppointmentController::autoCompleteSessions($consultant_id);
                    $appointments = AppointmentController::getConsultantAppointments($consultant_id);
                } else {
                    error_log("No consultant_id found for user_id: " . $_SESSION['user_id']);
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