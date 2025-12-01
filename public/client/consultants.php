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
    <title>Browse Consultants | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Available Consultants</h2>
    <div class="row">
        <!-- Example consultant card (Repeat with PHP loop)-->
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">[Consultant Name]</h5>
                    <h6 class="card-subtitle mb-2 text-muted">[Expertise]</h6>
                    <p class="card-text">[Short bio]</p>
                    <a href="book.php?consultant_id=[ID]" class="btn btn-primary btn-sm">Book</a>
                </div>
            </div>
        </div>
        <!-- end loop -->
    </div>
</div>
</body>
</html>