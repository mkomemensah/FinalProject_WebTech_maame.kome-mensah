<?php
require_once __DIR__ . '/../config/database.php';
class MessageController {
    public static function send($sender, $recipient, $content, $appointment_id = null) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, content, appointment_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sender, $recipient, $content, $appointment_id]);
        return ['success' => true];
    }
    public static function getThread($user1, $user2, $appointment_id = null) {
        global $pdo;
        $sql = "SELECT * FROM messages WHERE ((sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?))";
        $params = [$user1, $user2, $user2, $user1];
        if ($appointment_id) {
            $sql .= " AND appointment_id = ?";
            $params[] = $appointment_id;
        }
        $sql .= " ORDER BY sent_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public static function getConversations($user_id) {
        global $pdo;
        // Show recent conversation threads by unique other user
        $sql = "SELECT t.latest_id, m.sender_id, m.recipient_id, m.content, m.sent_at, u.name as user_name
                 FROM (
                    SELECT MAX(message_id) as latest_id
                    FROM messages
                    WHERE sender_id = ? OR recipient_id = ?
                    GROUP BY LEAST(sender_id, recipient_id), GREATEST(sender_id, recipient_id)
                 ) t
                 JOIN messages m ON m.message_id = t.latest_id
                 JOIN users u ON (u.user_id = IF(m.sender_id = ?, m.recipient_id, m.sender_id))
                 ORDER BY m.sent_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $user_id, $user_id]);
        return $stmt->fetchAll();
    }
}
