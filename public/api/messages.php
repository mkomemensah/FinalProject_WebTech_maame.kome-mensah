<?php
require_once __DIR__ . '/../../app/utils/session.php';
require_once __DIR__ . '/../../app/controllers/MessageController.php';
require_once __DIR__ . '/../../app/config/database.php';

secure_session_start();
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

switch ($action) {
    case 'users':
        // return potential recipients: if client -> consultants, if consultant -> clients
        if ($_SESSION['role'] === 'client') {
            // list approved consultants (same source as Find a Consultant)
            $stmt = $pdo->prepare("SELECT c.consultant_id, u.user_id, u.name, u.email, c.bio, c.years_of_experience, e.name AS expertise FROM consultants c JOIN users u ON u.user_id = c.user_id LEFT JOIN expertise e ON c.expertise_id = e.expertise_id WHERE c.profile_status = 'approved' AND u.status = 'active'");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $data = array_map(function($r){
                return [
                    'id' => $r['user_id'],
                    'consultant_id' => $r['consultant_id'],
                    'name' => $r['name'],
                    'email' => $r['email'],
                    'expertise' => $r['expertise'] ?? null,
                    'bio' => $r['bio'] ?? null,
                    'pic' => 'https://ui-avatars.com/api/?name='.urlencode($r['name']).'&background=0070b8&color=fff'
                ];
            }, $rows);
        } else {
            // list clients
            $stmt = $pdo->prepare("SELECT user_id, name, email FROM users WHERE role = 'client'");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $data = array_map(function($r){
                return ['id' => $r['user_id'], 'name' => $r['name'], 'email' => $r['email'], 'pic' => 'https://ui-avatars.com/api/?name='.urlencode($r['name']).'&background=0070b8&color=fff'];
            }, $rows);
        }
        echo json_encode(['success' => true, 'users' => $data]);
        break;

    case 'inbox':
        $convos = MessageController::getConversations($user_id);
        // collect partner ids and fetch names in batch
        $partners = [];
        foreach ($convos as $m) {
            $partner_id = ($m['sender_id'] == $user_id) ? $m['recipient_id'] : $m['sender_id'];
            $partners[$partner_id] = $partner_id;
        }
        $partner_data = [];
        if (!empty($partners)) {
            $placeholders = implode(',', array_fill(0, count($partners), '?'));
            $stmt = $pdo->prepare("SELECT user_id, name, email FROM users WHERE user_id IN ($placeholders)");
            $stmt->execute(array_values($partners));
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) { $partner_data[$r['user_id']] = $r; }
        }
        $out = [];
        foreach ($convos as $m) {
            $partner_id = ($m['sender_id'] == $user_id) ? $m['recipient_id'] : $m['sender_id'];
            $p = $partner_data[$partner_id] ?? null;
            $out[] = array_merge($m, [
                'partner_id' => $partner_id,
                'partner_name' => $p ? $p['name'] : null,
                'partner_email' => $p ? $p['email'] : null
            ]);
        }
        echo json_encode(['success' => true, 'conversations' => $out]);
        break;

    case 'thread':
        $with_id = intval($_GET['user_id'] ?? 0);
        if (!$with_id) { echo json_encode(['success' => false, 'error' => 'Missing user']); exit; }
        $messages = MessageController::getThread($user_id, $with_id);
        // mark messages as read where the current user is recipient
        try {
            $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND recipient_id = ? AND is_read = 0");
            $stmt->execute([$with_id, $user_id]);
        } catch (Exception $e) {
            // ignore marking errors
        }
        echo json_encode(['success' => true, 'messages' => $messages]);
        break;

    case 'send':
        $recipient = intval($_POST['recipient_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null;
        if (!$recipient || !$content) { echo json_encode(['success'=>false,'error'=>'Missing recipient or content.']); exit; }
        $resp = MessageController::send($user_id, $recipient, $content, $appointment_id);
        echo json_encode($resp);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
