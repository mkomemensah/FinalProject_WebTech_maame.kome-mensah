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
  <title>Admin | Manage Expertise</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3" style="color:#003A6C;">Expertise Categories</h2>
  <form method="post" action="<?= BASE_URL ?>api/admin.php?action=add_expertise" class="mb-4 card p-4 shadow-sm" style="max-width:480px;">
    <div class="mb-3">
      <label class="form-label">Expertise Name</label>
      <input name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Add Expertise</button>
  </form>
  <table class="table table-bordered shadow-sm">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Example row -->
      <tr>
        <td>Business Development</td>
        <td>Business growth, planning and sustainability.</td>
        <td>
          <a href="#" class="btn btn-sm btn-secondary disabled">Edit</a>
          <a href="#" class="btn btn-sm btn-danger disabled">Delete</a>
        </td>
      </tr>
      <!-- end PHP loop -->
    </tbody>
  </table>
</div>
</body>
</html>