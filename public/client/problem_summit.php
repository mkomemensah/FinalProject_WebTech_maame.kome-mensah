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
    <title>Submit Business Problem | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Describe Your Business Problem</h2>
    <form method="post" action="<?= BASE_URL ?>api/problems.php?action=submit" class="card p-4 shadow-sm" id="problemForm" novalidate>
        <div class="mb-3">
            <label class="form-label">Problem Description</label>
            <textarea name="description" class="form-control" rows="6" required maxlength="800"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Problem</button>
    </form>
</div>
</body>
</html>