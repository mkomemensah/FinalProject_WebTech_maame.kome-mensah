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
    <title>Consultant Dashboard | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4" style="color:#003A6C;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Set Availability</h4>
                <p>Update your open slots so clients can book you.</p>
                <a href="availability.php" class="btn btn-primary">Edit Availability</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>View Appointments</h4>
                <p>See your bookings and client details.</p>
                <a href="appointments.php" class="btn btn-outline-primary">Appointments</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Submit Feedback</h4>
                <p>Leave notes for clients after consultations.</p>
                <a href="feedback.php" class="btn btn-outline-secondary">Give Feedback</a>
            </div>
        </div>
    </div>
    <div class="mt-5 d-flex justify-content-between flex-wrap gap-2">
        <a href="profile.php" class="btn btn-link">Profile</a>
    </div>
</div>
</body>
</html>