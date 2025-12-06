<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$consultants = [
  [ 'name' => 'Dr. Anya Sharma', 'pic' => 'https://randomuser.me/api/portraits/women/68.jpg' ],
  [ 'name' => 'Kwame Yeboah', 'pic' => 'https://randomuser.me/api/portraits/men/74.jpg' ],
  [ 'name' => 'Ama Boateng', 'pic' => 'https://randomuser.me/api/portraits/women/85.jpg' ],
  [ 'name' => 'Jason Kraal', 'pic' => 'https://randomuser.me/api/portraits/men/21.jpg' ],
  [ 'name' => 'Maya Hassan', 'pic' => 'https://randomuser.me/api/portraits/women/43.jpg' ],
  [ 'name' => 'David Chen', 'pic' => 'https://randomuser.me/api/portraits/men/9.jpg' ],
];
$cid = isset($_GET['consultant_id']) ? intval($_GET['consultant_id'])-1 : 0;
$selected = $consultants[$cid] ?? $consultants[0];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  session_start();
  $consultid = intval($_POST['consultant_id']);
  $date = $_POST['date'];
  $time = $_POST['time'];
  $sessKey = 'booked_'.$consultid.'_'.$date.'_'.$time;
  if (isset($_SESSION[$sessKey])) $error = 'This time is already booked!';
  else {
    $_SESSION[$sessKey] = true;
    $success = true;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background: url('https://img.freepik.com/premium-photo/reading-books-online-open-book-laptop-blue-background-3d-render_407474-4746.jpg') center center / cover no-repeat fixed; position:relative;">
<div style="position:fixed;inset:0;z-index:1;background:rgba(255,255,255,0.72);"></div>
<div class="container py-4" style="max-width:540px; position:relative; z-index:2;">
  <h2 class="mb-3" style="color:#003A6C;">Book with <?= htmlspecialchars($selected['name']) ?></h2>
<?php if(!empty($success)): ?>
  <div class="alert alert-success">Booking request sent to consultant for confirmation!</div>
  <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
<?php else: ?>
  <?php if(!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post" class="card p-4 shadow-sm" novalidate>
    <input type="hidden" name="consultant_id" value="<?= $cid ?>">
    <div class="mb-3 text-center"><img src="<?= $selected['pic'] ?>" style="width:64px;height:64px;border-radius:50%;"><br>
      <b><?= htmlspecialchars($selected['name']) ?></b>
    </div>
    <div class="mb-3">
      <label class="form-label">Date *</label>
      <input name="date" type="date" required class="form-control" min="<?= date('Y-m-d') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Time *</label>
      <input name="time" type="time" required class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Meeting Notes (optional)</label>
      <textarea name="notes" class="form-control" rows="2" maxlength="255"></textarea>
    </div>
    <button type="submit" class="btn btn-primary w-100">Send Booking Request</button>
  </form>
<?php endif; ?>
</div>
</body>
</html>