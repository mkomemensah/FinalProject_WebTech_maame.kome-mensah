<?php
require_once __DIR__ . '/../../app/controllers/MessageController.php';
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
secure_session_start();
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
if (!$user_id) die(json_encode(['error'=>'Not logged in']));
header('Content-Type: application/json');
switch ($action) {
 case 'send':
  $recipient = intval($_POST['recipient_id'] ?? 0);
  $content = trim($_POST['content'] ?? '');
  $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null;
  if (!$recipient || !$content) die(json_encode(['success'=>false,'error'=>'Missing recipient or content.']));
  $resp = MessageController::send($user_id, $recipient, $content, $appointment_id);
  echo json_encode($resp);
  break;
 case 'thread':
  $with_id = intval($_GET['user_id'] ?? 0);
  $appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : null;
  if (!$with_id) die(json_encode(['success'=>false,'error'=>'Missing user.']));
  $messages = MessageController::getThread($user_id, $with_id, $appointment_id);
  echo json_encode(['success'=>true,'messages'=>$messages]);
  break;
 case 'inbox':
  $convos = MessageController::getConversations($user_id);
  echo json_encode(['success'=>true,'conversations'=>$convos]);
  break;
 default:
  echo json_encode(['error'=>'Unknown action.']);
}
