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
    <title>Set Availability | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Set Your Availability</h2>
    <form method="post" action="<?= BASE_URL ?>api/availability.php?action=set" class="card p-4 shadow-sm" novalidate>
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">End Time</label>
            <input type="time" name="end_time" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Slot</button>
    </form>
    <!-- Table of available slots (to be dynamically filled) -->
</div>
</body>
</html>