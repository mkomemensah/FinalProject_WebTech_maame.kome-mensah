<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
$client_id = $_SESSION['user_id'];
$appointments = AppointmentController::getClientAppointments($client_id);
function statusBadge($status) {
  switch($status) {
    case 'pending': return '<span class="badge bg-warning text-dark ms-2">Pending</span>';
    case 'confirmed': return '<span class="badge bg-success text-dark ms-2">Confirmed</span>';
    case 'completed': return '<span class="badge bg-primary text-white ms-2">Completed</span>';
    case 'cancelled': return '<span class="badge bg-danger text-white ms-2">Cancelled</span>';
    default: return '';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Booking History | ConsultEASE</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    .appt-card{border-radius:16px;box-shadow:0 1px 10px #003a6c21;margin-bottom:22px;background:#fff;}
    .appt-img{width:54px;height:54px;border-radius:50%;object-fit:cover;box-shadow:0 1px 3px #8883;margin-right:22px;}
    .appt-card .badge{font-size:0.97em;vertical-align:middle;}
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-3" style="color:#003A6C;font-weight:800;">My Booking History</h2>
  <?php if(!$appointments): ?><div class="alert alert-info">You have no bookings yet.</div><?php endif; ?>
  <div class="row">
    <?php foreach($appointments as $appt):
        $img = $appt['pic'] ?? '../assets/images/default-avatar.png';
    ?>
    <div class="col-lg-8">
      <div class="card appt-card">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center">
          <img src="<?= htmlspecialchars($img) ?>" class="appt-img me-3">
          <div style="flex:1 1 0;">
            <b><?= htmlspecialchars($appt['consultant_name']) ?></b>
              <?= statusBadge($appt['status']) ?>
            <div class="small text-muted">Date: <?= htmlspecialchars($appt['date']) ?> | Time: <?= htmlspecialchars($appt['start_time']) ?> - <?= htmlspecialchars($appt['end_time']) ?></div>
            <?php if ($appt['status']==='completed'): ?>
              <?php if($appt['client_notes']): ?><div class="mt-2 small text-secondary">Your feedback: <?= htmlspecialchars($appt['client_notes']) ?></div><?php endif; ?>
              <?php if($appt['consultant_notes']): ?><div class="mt-1 small text-success">Consultant feedback: <?= htmlspecialchars($appt['consultant_notes']) ?></div><?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <a href="dashboard.php" class="btn btn-outline-primary mt-4">Back to Dashboard</a>
</div>
</body>
</html>
