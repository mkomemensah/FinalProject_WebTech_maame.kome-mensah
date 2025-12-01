<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | System Settings</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3" style="color:#003A6C;">System Settings</h2>
  <div class="card p-4 shadow-sm">
    <h5>Settings management coming soon…</h5>
    <p>This is a placeholder for admin system configuration (permissions, platform settings, etc.).</p>
  </div>
</div>
</body>
</html>