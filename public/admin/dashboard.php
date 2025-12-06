<?php
require_once __DIR__.'/../../app/middleware/auth_middleware.php';
require_once __DIR__.'/../../app/config/database.php';
if ($_SESSION['role'] !== 'admin') die('Access denied');
// User count
$users = $pdo->query("SELECT * FROM users")->fetchAll();
$consultants = $pdo->query("SELECT * FROM users WHERE role='consultant'")->fetchAll();
$pending_consultants = $pdo->query("SELECT u.*, c.profile_status FROM users u JOIN consultants c ON u.user_id=c.user_id WHERE u.role='consultant' AND c.profile_status='pending'")->fetchAll();
$appointments = $pdo->query("SELECT a.*, u.name as client_name, c.user_id as consultant_user_id, u2.name as consultant_name FROM appointments a JOIN users u ON a.client_id = u.user_id JOIN consultants c ON a.consultant_id=c.consultant_id JOIN users u2 ON c.user_id=u2.user_id")->fetchAll();
$expertise = $pdo->query("SELECT * FROM expertise")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Console | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
body {background:linear-gradient(120deg,#b0e0ef 0%,#dbcef6 100%);min-height:100vh;}
.navbar-admin{background:#073165;padding:1rem 2vw;margin-bottom:1.7rem;box-shadow:0 2px 18px #07316515;}
.navbar-admin .nav-link,.navbar-admin .navbar-brand{color:#fff!important;font-weight:600;letter-spacing:.4px;}
.glass-card{background:rgba(255,255,255,.97);backdrop-filter:blur(3px);border-radius:18px;padding:2rem 1.35rem;box-shadow:0 7px 35px #2e09527b;}
.section-h{font-size:1.3rem;font-weight:700;margin-bottom:.5rem;letter-spacing:.01rem;}
.btn.blue-accent{background:#059cff!important;color:#fff;font-weight:600;border-radius:12px;}
.card-action{margin-top:.7em;margin-left:3em;}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-admin mb-4">
 <div class="container-fluid">
   <a class="navbar-brand" href="#">ConsultEASE</a>
   <ul class="navbar-nav flex-row gap-3 ms-auto align-items-center">
     <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
     <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
     <li class="nav-item"><a class="nav-link" href="consultants.php">Consultants</a></li>
     <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
     <li class="nav-item"><a class="nav-link" href="expertise.php">Expertise</a></li>
     <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
     <li class="nav-item"><a class="nav-link btn btn-light text-primary fw-bold ms-2 px-4" href="../api/auth.php?action=logout">Logout</a></li>
   </ul>
 </div>
</nav>
<div class="container mb-5">
  <div class="row g-4 align-items-stretch mb-4">
    <!-- Manage Users -->
    <div class="col-md-4">
      <div class="glass-card">
        <div class="section-h">Manage Users</div>
        <p class="text-muted mb-2">Suspend, delete, or view user accounts.</p>
        <a href="users.php" class="btn btn-primary w-100 mb-2">View All Users</a>
        <div class="mt-3"><span class="fw-semibold">Total:</span> <?=count($users)?></div>
      </div>
    </div>
    <!-- Manage Consultants -->
    <div class="col-md-4">
      <div class="glass-card">
        <div class="section-h">Manage Consultants</div>
        <p class="text-muted mb-2">Approve or suspend consultants.<br>Pending: <span class="fw-bold text-danger"><?=count($pending_consultants)?></span></p>
        <a href="consultants.php" class="btn btn-primary w-100 mb-2">View/Approve Consultants</a>
        <div class="mt-3"><span class="fw-semibold">Consultants:</span> <?=count($consultants)?></div>
      </div>
    </div>
    <!-- Oversee Appointments -->
    <div class="col-md-4">
      <div class="glass-card">
        <div class="section-h">Oversee Appointments</div>
        <p class="text-muted mb-2">View all bookings, approve, resolve, delete records.</p>
        <a href="appointments.php" class="btn btn-primary w-100 mb-2">See All Appointments</a>
        <div class="mt-3"><span class="fw-semibold">Booked:</span> <?=count($appointments)?></div>
      </div>
    </div>
  </div>
  <div class="row g-4 align-items-stretch mb-3">
    <!-- Manage Expertise -->
    <div class="col-md-5">
      <div class="glass-card">
        <div class="section-h">Manage Expertise</div>
        <p class="text-muted mb-2">Add, edit, or delete expertise categories.</p>
        <a href="expertise.php" class="btn btn-primary w-100 mb-2">Edit Expertise</a>
        <div class="mt-3"><span class="fw-semibold">Categories:</span> <?=count($expertise)?></div>
      </div>
    </div>
    <!-- System Maintenance -->
    <div class="col-md-7">
      <div class="glass-card">
        <div class="section-h">System Maintenance</div>
        <p class="text-muted mb-2">System settings, logs, integrity, and reports.</p>
        <a href="settings.php" class="btn btn-outline-primary w-100 mb-2">Manage System</a>
        <div class="d-flex flex-wrap gap-2 mt-2">
          <a href="logs.php" class="btn btn-outline-secondary">View Logs</a>
          <a href="integrity.php" class="btn btn-outline-secondary">Data Integrity Check</a>
          <a href="#" class="btn btn-outline-secondary disabled">Run Reports</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>