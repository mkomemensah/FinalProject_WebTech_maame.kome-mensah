<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultant Profile | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 style="color:#003A6C;">My Consultant Profile</h2>
    <form class="card p-4 shadow-sm" method="post" action="<?= BASE_URL ?>api/consultants.php?action=update_profile" novalidate>
        <!-- Use PHP to pre-populate fields -->
        <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea class="form-control" name="bio" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Years of Experience</label>
            <input type="number" name="years_of_experience" min="0" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
</body>
</html>