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
    <title>Book Appointment | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Book an Appointment</h2>
    <form method="post" action="<?= BASE_URL ?>api/appointments.php?action=book" class="card p-4 shadow-sm" id="bookForm" novalidate>
        <div class="mb-3">
            <label class="form-label">Consultant</label>
            <!-- This should be dynamically loaded from available consultants via PHP/JS/AJAX -->
            <select name="consultant_id" class="form-select" required>
                <option value="">Select Consultant</option>
                <!-- php: echo options -->
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Time</label>
            <input type="time" name="time" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Book Appointment</button>
    </form>
</div>
</body>
</html>