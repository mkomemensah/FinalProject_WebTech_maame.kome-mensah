<?php
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

secure_session_start();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'book':
        require_role('client');
        $data = [
            'consultant_id' => $_POST['consultant_id'] ?? null,
            'availability_id' => $_POST['availability_id'] ?? null,
            'client_id' => $_SESSION['user_id'],
            'date' => $_POST['date'] ?? null,
            'start_time' => $_POST['start_time'] ?? null,
            'end_time' => $_POST['end_time'] ?? null
        ];
        $result = AppointmentController::book($data);
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    case 'list':
        // AJAX table fill for clients/consultants/admins
        if ($_SESSION['role'] === 'client') {
            $appointments = AppointmentController::getClientAppointments($_SESSION['user_id']);
        } elseif ($_SESSION['role'] === 'consultant') {
            // Auto-complete sessions for this consultant
            global $pdo;
            $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            $consultant_id = $row ? $row['consultant_id'] : null;
            if ($consultant_id) {
                AppointmentController::autoCompleteSessions($consultant_id);
                $appointments = AppointmentController::getConsultantAppointments($consultant_id);
            } else {
                $appointments = [];
            }
        } elseif ($_SESSION['role'] === 'admin') {
            AppointmentController::autoCompleteSessions(); // global auto-mark
            $appointments = AppointmentController::getAllAppointments();
        } else {
            $appointments = [];
        }
        header('Content-Type: application/json');
        echo json_encode($appointments);
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