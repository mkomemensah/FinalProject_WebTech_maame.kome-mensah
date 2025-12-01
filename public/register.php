<?php require_once __DIR__.'/../app/config/database.php'; ?>
<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="UTF-8">
  <title>ConsultEASE | Register</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/validation.js" defer></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>">ConsultEASE</a>
    </div>
  </nav>
  <div class="container d-flex align-items-center justify-content-center" style="min-height:70vh;">
    <div class="card p-4 shadow" style="min-width:410px;">
      <h3 class="mb-3 text-center">Create Account</h3>
      <form id="registerForm" action="<?= BASE_URL ?>api/auth.php?action=register" method="post" novalidate autocomplete="off">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input name="name" type="text" class="form-control" required>
          <div class="invalid-feedback" id="nameError"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
          <div class="invalid-feedback" id="emailError"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input name="phone" type="text" class="form-control" required>
          <div class="invalid-feedback" id="phoneError"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
          <div class="invalid-feedback" id="passwordError"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Register as</label>
          <select name="role" class="form-select" required>
            <option value="client">Client</option>
            <option value="consultant">Consultant</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>
    </div>
  </div>
</body>
</html>