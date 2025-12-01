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
    <title>Admin Dashboard | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4" style="color:#003A6C;">Admin Console</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Manage Users</h4>
                <p>See all users and their access levels.</p>
                <a href="users.php" class="btn btn-primary">Users</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Manage Consultants</h4>
                <p>Approve, suspend, or edit consultant profiles.</p>
                <a href="consultants.php" class="btn btn-outline-primary">Consultants</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Appointments</h4>
                <p>View, confirm, or cancel all appointments.</p>
                <a href="appointments.php" class="btn btn-outline-secondary">Appointments</a>
            </div>
        </div>
    </div>
    <div class="mt-5 d-flex gap-2 flex-wrap">
        <a href="expertise.php" class="btn btn-link">Expertise</a>
        <a href="settings.php" class="btn btn-link">Settings</a>
    </div>
</div>
</body>
</html>