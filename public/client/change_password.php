<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
// For demo: on POST, check new/confirm match & show result; no real password update.
$msg = '';$type='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $np = trim($_POST['new_password']); $cp = trim($_POST['confirm_password']);
  if(!$np||!$cp) {$msg = "All fields required.";$type='danger';}
  elseif($np!==$cp) {$msg="Passwords do not match.";$type='danger';}
  elseif(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $np)) { $msg="Weak password: 8+, upper, lower, number, symbol.";$type='danger'; }
  else {$msg="Password updated successfully (demo, not real change).";$type='success';}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Change Password | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4" style="max-width:460px;">
  <h2 class="mb-3" style="color:#003A6C;">Change Password</h2>
  <?php if($msg): ?><div class="alert alert-<?= $type ?>"> <?= htmlspecialchars($msg) ?> </div><?php endif; ?>
  <form method="post" class="card p-4 shadow-sm">
    <div class="mb-3"><label class="form-label">New Password</label>
      <input type="password" name="new_password" class="form-control" required minlength="8"></div>
    <div class="mb-3"><label class="form-label">Confirm New Password</label>
      <input type="password" name="confirm_password" class="form-control" required minlength="8"></div>
    <ul class="small text-muted mb-3">
      <li>Min 8 characters, upper/lower, number, symbol</li>
    </ul>
    <button class="btn btn-primary w-100">Update Password</button>
  </form>
  <a href="dashboard.php" class="btn btn-outline-primary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
