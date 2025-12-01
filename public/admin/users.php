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
  <title>Admin | Manage Users</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3" style="color:#003A6C;">Users Management</h2>
  <table class="table table-bordered shadow-sm">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Use PHP loop to display users -->
      <!-- Example Row -->
      <tr>
        <td>Jane Doe</td>
        <td>janedoe@email.com</td>
        <td>client</td>
        <td>active</td>
        <td>2024-05-01</td>
        <td>
          <a href="#" class="btn btn-sm btn-secondary disabled">Edit</a>
          <a href="#" class="btn btn-sm btn-danger disabled">Suspend</a>
        </td>
      </tr>
      <!-- end PHP loop -->
    </tbody>
  </table>
</div>
</body>
</html>