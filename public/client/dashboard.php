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
    <title>Client Dashboard | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4" style="color:#003A6C;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Book Appointment</h4>
                <p>Easily schedule a consultation with our expert consultants.</p>
                <a href="book.php" class="btn btn-primary">Book Now</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>My Appointments</h4>
                <p>View or manage your previous and upcoming bookings.</p>
                <a href="appointments.php" class="btn btn-outline-primary">Appointments</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h4>Give Feedback</h4>
                <p>Help us improve by reviewing your last consulting session.</p>
                <a href="feedback.php" class="btn btn-outline-secondary">Submit Feedback</a>
            </div>
        </div>
    </div>
    <div class="mt-5 d-flex justify-content-between flex-wrap gap-2">
        <a href="consultants.php" class="btn btn-link">Browse Consultants</a>
        <a href="problem_submit.php" class="btn btn-link">Submit Business Problem</a>
        <a href="profile.php" class="btn btn-link">Profile</a>
    </div>
</div>
</body>
</html>