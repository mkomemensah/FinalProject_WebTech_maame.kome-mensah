<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">My Profile</h2>
    <form class="card p-4 shadow-sm" method="post" action="<?= BASE_URL ?>api/auth.php?action=update_profile" novalidate>
        <!-- Use PHP to pre-populate fields with user data -->
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" type="text" class="form-control" required value="<?= htmlspecialchars($_SESSION['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="text" class="form-control" disabled value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" type="text" class="form-control" value="">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
</body>
</html>