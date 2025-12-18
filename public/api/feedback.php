<?php
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';

// Start secure session and check if user is logged in
secure_session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Get action from query string
$action = $_GET['action'] ?? '';

// Handle CORS if needed
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
    exit(0);
}

// Helper function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Handle different actions
switch ($action) {
    case 'submit':
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            sendResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        // Get the raw POST data
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        // Check if this is a client feedback submission (from the consultants page)
        if (isset($input['consultant_id']) && isset($input['rating'])) {
            // Client feedback for a consultant
            $data = [
                'client_id' => $_SESSION['user_id'],
                'consultant_id' => filter_var($input['consultant_id'], FILTER_VALIDATE_INT),
                'rating' => filter_var($input['rating'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 5]
                ]),
                'comments' => filter_var($input['comments'] ?? '', FILTER_SANITIZE_STRING),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate required fields
            if (!$data['consultant_id'] || !$data['rating']) {
                sendResponse(['success' => false, 'error' => 'Invalid input data'], 400);
            }
            
            // Here you would typically save this to your database
            // For now, we'll just return a success response
            $result = [
                'success' => true,
                'message' => 'Thank you for your feedback!',
                'data' => $data
            ];
            
            sendResponse($result);
            
        } 
        // Existing feedback handling for appointments
        else if (isset($input['appointment_id'])) {
            try {
                if (isset($input['consultant_notes'])) {
                    require_role('consultant');
                    $data = [
                        'appointment_id' => filter_var($input['appointment_id'], FILTER_VALIDATE_INT),
                        'consultant_notes' => trim($input['consultant_notes'] ?? '')
                    ];
                    
                    if (!$data['appointment_id'] || empty($data['consultant_notes'])) {
                        sendResponse(['success' => false, 'error' => 'Appointment ID and consultant notes are required.'], 400);
                    }
                    
                    $result = AppointmentController::submitFeedback($data);
                } else if (isset($input['client_notes'])) {
                    require_role('client');
                    $data = [
                        'appointment_id' => filter_var($input['appointment_id'], FILTER_VALIDATE_INT),
                        'client_notes' => trim($input['client_notes'] ?? '')
                    ];
                    
                    if (!$data['appointment_id'] || empty($data['client_notes'])) {
                        sendResponse(['success' => false, 'error' => 'Appointment ID and client notes are required.'], 400);
                    }
                    
                    $result = AppointmentController::submitClientFeedback($data);
                } else {
                    sendResponse(['success' => false, 'error' => 'No feedback provided.'], 400);
                }
                
                sendResponse($result);
            } catch (Exception $e) {
                error_log("Error submitting feedback: " . $e->getMessage());
                sendResponse(['success' => false, 'error' => 'Failed to submit feedback: ' . $e->getMessage()], 500);
            }
        } else {
            sendResponse(['success' => false, 'error' => 'Invalid request'], 400);
        }
        break;
        
    case 'get':
        // Get feedback for a consultant
        if (isset($_GET['consultant_id'])) {
            $consultantId = filter_var($_GET['consultant_id'], FILTER_VALIDATE_INT);
            if (!$consultantId) {
                sendResponse(['success' => false, 'error' => 'Invalid consultant ID'], 400);
            }
            
            // Here you would typically fetch feedback from your database
            // For now, we'll return sample data
            $result = [
                'success' => true,
                'average_rating' => 4.5,
                'total_reviews' => 12,
                'recent_reviews' => [
                    [
                        'rating' => 5,
                        'comments' => 'Great consultant, very helpful!',
                        'client_name' => 'John D.',
                        'date' => '2023-12-10'
                    ],
                    // Add more sample reviews as needed
                ]
            ];
            
            sendResponse($result);
        }
        break;
        
    default:
        sendResponse(['success' => false, 'error' => 'Invalid action'], 400);
}