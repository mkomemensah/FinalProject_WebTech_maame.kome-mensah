<?php
require_once __DIR__ . '/../config/database.php';

class ConsultantController {
    public static function getAllPublic() {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT c.consultant_id, u.name, u.email, c.bio, c.years_of_experience, c.profile_status, e.name AS expertise
             FROM consultants c
             JOIN users u ON c.user_id = u.user_id
             LEFT JOIN expertise e ON c.expertise_id = e.expertise_id
             WHERE c.profile_status = 'approved' AND u.status = 'active'"
        );
        $stmt->execute();
        $consultants = $stmt->fetchAll();
        $consultants = array_filter($consultants, function($c) {
            $name = strtolower($c['name']);
            return (
                strpos($name, 'abena koomson') === false &&
                strpos($name, 'kwesi sarkodie') === false &&
                strpos($name, 'ama osei') === false
            );
        });
        // Reindex array
        $consultants = array_values($consultants);
        foreach ($consultants as &$c) {
            $name = strtolower($c['name']);
            if (strpos($name, 'afrakoma') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/women/65.jpg';
            else if (strpos($name, 'akua') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/women/66.jpg';
            else if (strpos($name, 'adwoa') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/women/67.jpg';
            else if (strpos($name, 'ashley') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/women/68.jpg';
            else if (strpos($name, 'adubea') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/women/69.jpg';
            else if (strpos($name, 'colette') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/women/70.jpg';
            else if (strpos($name, 'rosby') !== false) $c['pic'] = 'https://randomuser.me/api/portraits/men/75.jpg';
            else $c['pic'] = '../assets/images/default-avatar.png';
        }
        return $consultants;
    }
    public static function updateProfile($data) {
        global $pdo;
        // Find consultant row from user_id
        $stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ? LIMIT 1");
        $stmt->execute([$data['user_id']]);
        $consultant = $stmt->fetch();
        if (!$consultant) return ['success'=>false, 'error'=>'Consultant not found'];
        $consultant_id = $consultant['consultant_id'];
        // Fix: years_of_experience must be integer or NULL
        $years = (isset($data['years_of_experience']) && is_numeric($data['years_of_experience']) && $data['years_of_experience'] !== '') ? intval($data['years_of_experience']) : null;
        $stmt = $pdo->prepare("UPDATE consultants SET bio = ?, years_of_experience = ? WHERE consultant_id = ?");
        $stmt->execute([
            $data['bio'],
            $years,
            $consultant_id
        ]);
        // Fetch updated row
        $stmt = $pdo->prepare("SELECT bio, years_of_experience FROM consultants WHERE consultant_id = ?");
        $stmt->execute([$consultant_id]);
        $updated = $stmt->fetch();
        return ['success'=>true, 'bio'=>$updated['bio'], 'years_of_experience'=>$updated['years_of_experience']];
    }
    public static function addAvailability($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO availability (consultant_id, date, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['consultant_id'], $data['date'], $data['start_time'], $data['end_time']]);
        return ['success'=>true];
    }
    public static function getAvailability($consultant_id) {/* ... */}
    // Publicly list only available and future slots for a consultant
    public static function getAvailabilityPublic($consultant_id) {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT availability_id, date, start_time, end_time FROM availability
             WHERE consultant_id = ? AND status = 'available' AND (date > CURDATE() OR (date = CURDATE() AND end_time > CURTIME()))
             ORDER BY date ASC, start_time ASC"
        );
        $stmt->execute([$consultant_id]);
        return $stmt->fetchAll();
    }
}