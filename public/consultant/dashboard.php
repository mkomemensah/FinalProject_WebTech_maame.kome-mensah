<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
require_once __DIR__ . '/../../app/config/database.php';
$consultant_id = $_SESSION['user_id'];
// Get live counts and records
$stats = [
 'pending_requests' => 0,
 'upcoming_sessions' => 0,
 'pending_feedback' => 0,
 'avail_slots' => 0,
];
$upcoming = $pending = $feedbacks = $slots = [];
$now = date('Y-m-d H:i:s');
// 1. Upcoming Sessions (confirmed, future)
$stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name AS client_name, u.email, u.role 
    FROM appointments a 
    JOIN availability v ON a.availability_id = v.availability_id 
    JOIN users u ON a.client_id = u.user_id 
    WHERE a.consultant_id = ? AND a.status = 'confirmed' 
    AND CONCAT(v.date, ' ', v.start_time) >= ?
    ORDER BY v.date, v.start_time LIMIT 6");
$stmt->execute([$consultant_id, $now]);
$upcoming = $stmt->fetchAll();
$stats['upcoming_sessions'] = count($upcoming);
// 2. Pending Approvals (pending bookings)
$stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name as client_name 
    FROM appointments a 
    JOIN availability v ON a.availability_id = v.availability_id 
    JOIN users u ON a.client_id = u.user_id 
    WHERE a.consultant_id = ? AND a.status = 'pending' 
    ORDER BY v.date, v.start_time");
$stmt->execute([$consultant_id]);
$pending = $stmt->fetchAll();
$stats['pending_requests'] = count($pending);
// 3. Availability slots
$stmt = $pdo->prepare("SELECT * FROM availability WHERE consultant_id = ? AND (date > CURDATE() OR (date = CURDATE() AND end_time >= CURTIME())) ORDER BY date, start_time");
$stmt->execute([$consultant_id]);
$slots = $stmt->fetchAll();
$stats['avail_slots'] = count($slots);
// 4. Feedback needed (completed appts missing feedback)
$stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name as client_name 
    FROM appointments a 
    JOIN availability v ON a.availability_id = v.availability_id 
    JOIN users u ON a.client_id = u.user_id 
    LEFT JOIN feedback f ON f.appointment_id = a.appointment_id 
    WHERE a.consultant_id = ? AND a.status = 'completed' AND f.feedback_id IS NULL");
$stmt->execute([$consultant_id]);
$feedbacks = $stmt->fetchAll();
$stats['pending_feedback'] = count($feedbacks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Consultant Dashboard | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
<style>
body {background:radial-gradient(ellipse 110% 90% at 70% 0,#cce2f7 40%,#e6f0fc 100%);}
.dash-card {background:#fff;border-radius:24px;padding:2rem 1.7rem;box-shadow:0 6px 32px #003a6c17;min-height:180px;}
.dash-header {font-size:2.1rem;color:#003A6C;font-weight:700;margin-bottom:0.6rem}
.stat-badge {background:#f0f5fa;border-radius:2rem;color:#0070b8;font-weight:700;font-size:1.07rem;padding:0.3rem 0.9rem;} .pending-badge{background:#ffd700;color:#222;}
.avatar {width:46px;height:46px;border-radius:50%;object-fit:cover;}
</style>
</head>
<body>
<div class="container py-4">
  <div class="mb-4">
    <span class="dash-header">Welcome, <?= htmlspecialchars($_SESSION['name']) ?> </span>
    <span class="ms-2 stat-badge">Upcoming: <?= $stats['upcoming_sessions'] ?></span>
    <span class="ms-1 stat-badge" style="background:#ffe0af;">Pending: <?= $stats['pending_requests'] ?></span>
    <span class="ms-1 stat-badge" style="background:#e0f7fc;">Feedback: <?= $stats['pending_feedback'] ?></span>
  </div>
  <div class="row g-4 mb-3">
    <div class="col-lg-6">
      <div class="dash-card">
        <div class="fw-bold mb-2 fs-5"><i class="bi bi-calendar-check"></i> Upcoming Sessions</div>
        <div class="row gy-2">
          <?php foreach($upcoming as $appt): ?>
            <div class="col-md-6 col-12">
              <div class="p-2 rounded border mb-1 bg-light">
                <div class="d-flex align-items-center gap-2"><i class="bi bi-person-badge me-2"></i>
                  <b><?= htmlspecialchars($appt['client_name']) ?></b>
                </div>
                <div class="ms-4 mb-1 text-muted small">
                  <?= htmlspecialchars($appt['date']) ?> @ <?= htmlspecialchars($appt['start_time']) ?>
                </div>
                <div class="ms-4 small"><a href="appointments.php#appt-<?= $appt['id'] ?>" class="btn btn-sm btn-outline-info">View</a></div>
              </div>
            </div>
          <?php endforeach; if(!$upcoming): ?>
            <div class="text-secondary ps-2">No sessions scheduled.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="dash-card">
        <div class="fw-bold mb-2 fs-5"><i class="bi bi-hourglass-split"></i> Pending Approvals</div>
        <div class="row gy-2">
          <?php foreach($pending as $req): ?>
            <div class="col-12 mb-2">
              <div class="p-2 rounded border mb-1 bg-light">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <b><?= htmlspecialchars($req['client_name']) ?></b> <span class="pending-badge ms-2 px-2">Pending</span>
                </div>
                <div class="ms-4 small">Date: <?= htmlspecialchars($req['date']) ?> <?= htmlspecialchars($req['start_time']) ?></div>
                <div class="ms-4 mt-1">
                  <a href="appointments.php#appt-<?= $req['id'] ?>" class="btn btn-sm btn-success">Approve</a>
                  <a href="appointments.php#appt-<?= $req['id'] ?>" class="btn btn-sm btn-danger ms-1">Reject</a>
                </div>
              </div>
            </div>
          <?php endforeach; if(!$pending): ?>
            <div class="text-secondary ps-2">No pending requests.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-4 mt-1">
    <div class="col-lg-5">
      <div class="dash-card">
        <div class="fw-bold mb-2 fs-5"><i class="bi bi-calendar-plus"></i> Manage Availability</div>
        <div>Slots available: <span class="badge bg-info text-dark ms-2"><?= $stats['avail_slots'] ?></span></div>
        <div class="mt-2">
          <a href="availability.php" class="btn btn-outline-primary w-100">Edit Calendar & Slots</a>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="dash-card">
        <div class="fw-bold mb-2 fs-5"><i class="bi bi-chat-left-quote"></i> Feedback</div>
        <div class="row gy-2">
          <?php foreach($feedbacks as $todo): ?>
            <div class="col-12 mb-2">
              <div class="p-2 rounded border bg-light">
                <div class="d-flex align-items-center gap-2">
                  <b><?= htmlspecialchars($todo['client_name']) ?></b>
                </div>
                <div class="ms-4 small text-muted">On: <?= htmlspecialchars($todo['date']) ?> <?= htmlspecialchars($todo['start_time']) ?></div>
                <div class="ms-4 mt-1">
                  <a href="feedback.php?appointment_id=<?= $todo['id'] ?>" class="btn btn-sm btn-primary">Leave Feedback</a>
                </div>
              </div>
            </div>
          <?php endforeach; if(!$feedbacks): ?>
            <div class="text-secondary ps-2">No feedback pending.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4 row g-3">
    <div class="col-md-4">
      <a href="profile.php" class="btn btn-outline-dark btn-lg w-100"><i class="bi bi-person"></i> Edit Profile</a>
    </div>
    <div class="col-md-4">
      <a href="appointments.php" class="btn btn-outline-dark btn-lg w-100"> All Appointments </a>
    </div>
    <div class="col-md-4">
      <a href="../api/auth.php?action=logout" class="btn btn-danger btn-lg w-100">Logout</a>
    </div>
  </div>
</div>
<!-- TODO: add pop-up JS for new requests, accept/reject, etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>