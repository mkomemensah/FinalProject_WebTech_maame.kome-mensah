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
    <title>Submit Feedback | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Leave Feedback for Appointment</h2>
    <form method="post" action="<?= BASE_URL ?>api/feedback.php?action=submit" class="card p-4 shadow-sm" novalidate>
        <div class="mb-3">
            <label class="form-label">Select Appointment</label>
            <select name="appointment_id" class="form-select" required>
                <option value="">Select Appointment</option>
                <!-- Loop PHP: upcoming/completed appointments -->
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="consultant_notes" rows="5" maxlength="600" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>
</div>
</body>
</html>