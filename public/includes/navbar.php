<?php
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/utils/session.php';
secure_session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>">ConsultEASE</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBarMain" aria-controls="navBarMain" aria-expanded="false" aria-label="Toggle nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navBarMain">
      <ul class="navbar-nav ms-auto">
        <?php if ($_SESSION['role'] === 'client'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>client/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>client/consultants.php">Consultants</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>client/book.php">Book Appointment</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>client/messages.php">Messages</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>client/profile.php">Profile</a></li>
        <?php elseif ($_SESSION['role'] === 'consultant'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>consultant/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>consultant/messages.php">Messages</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>consultant/profile.php">Profile</a></li>
        <?php elseif ($_SESSION['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/consultants.php">Consultants</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/appointments.php">Appointments</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/expertise.php">Expertise</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/settings.php">Settings</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link btn btn-light btn-sm" href="<?= BASE_URL ?>api/auth.php?action=logout" style="margin-left:10px;">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>