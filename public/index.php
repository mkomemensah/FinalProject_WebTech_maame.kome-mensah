<?php
require_once __DIR__.'/../app/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ConsultEASE | Home</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>">ConsultEASE</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="container hero text-center">
    <h1 class="display-4 mb-3" style="color:#003A6C">Unlock Business Potential</h1>
    <p class="lead mb-4" style="color:#0070B8">Book top consultants on demand for your business needs.<br>Smart, secure, as easy as Deloitte.</p>
    <a href="<?= BASE_URL ?>login.php" class="btn btn-primary btn-lg">Get Started</a>
  </section>

  <footer class="mt-5">
    © ConsultEASE <?= date('Y') ?> | All Rights Reserved.
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>