<?php
require_once __DIR__.'/../../app/middleware/auth_middleware.php';
require_once __DIR__.'/../../app/config/database.php';
if ($_SESSION['role'] !== 'admin') die('Access denied');
// User count
$users = $pdo->query("SELECT * FROM users")->fetchAll();
$consultants = [];
$pending_consultants = [];
$appointments = $pdo->query("SELECT a.*, u.name as client_name, c.user_id as consultant_user_id, u2.name as consultant_name FROM appointments a JOIN users u ON a.client_id = u.user_id JOIN consultants c ON a.consultant_id=c.consultant_id JOIN users u2 ON c.user_id=u2.user_id")->fetchAll();
$expertise = $pdo->query("SELECT * FROM expertise")->fetchAll();
// Get audit count if the table exists; avoid fatal error when DB hasn't been migrated yet
$audit_count = 0;
try {
  $audit_count = (int)$pdo->query("SELECT COUNT(*) FROM admin_audit")->fetchColumn();
} catch (PDOException $e) {
  // Table missing or other DB error; keep audit_count = 0 and log the issue
  error_log('Audit count query failed: ' . $e->getMessage());
}
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
     <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
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
    <!-- Manage Consultants card removed per admin preference -->
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
    <!-- Logs / System Maintenance -->
    <div class="col-md-12">
      <div class="glass-card">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="section-h">System Maintenance & Logs</div>
            <p class="text-muted mb-2">System settings, audit logs, integrity checks, and reports.</p>
            <div class="mt-2"><span class="fw-semibold">Audit entries:</span> <?= $audit_count ?></div>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="audit.php" class="btn btn-primary px-4 py-2">View Logs</a>
            <div class="mt-2 d-inline-block">
              <a href="integrity.php" class="btn btn-outline-secondary btn-sm">Data Integrity</a>
              <a href="settings.php" class="btn btn-outline-secondary btn-sm">Settings</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>