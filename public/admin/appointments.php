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
  <title>Admin | Appointments</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3" style="color:#003A6C;">All Appointments</h2>
  <table class="table table-bordered shadow-sm">
    <thead class="table-light">
      <tr>
        <th>Client</th>
        <th>Consultant</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Example row -->
      <tr>
        <td>Esi Mensah</td>
        <td>Abena Koomson</td>
        <td>2024-07-12</td>
        <td>14:00</td>
        <td>pending</td>
        <td>
          <a href="#" class="btn btn-sm btn-success disabled">Confirm</a>
          <a href="#" class="btn btn-sm btn-danger disabled">Cancel</a>
        </td>
      </tr>
      <!-- end PHP loop -->
    </tbody>
  </table>
</div>
</body>
</html>