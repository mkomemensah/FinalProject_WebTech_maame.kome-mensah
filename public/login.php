<?php require_once __DIR__.'/../app/config/database.php'; ?>
<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="UTF-8">
  <title>ConsultEASE | Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/validation.js" defer></script>
</head>
<body style="background: url('https://media.istockphoto.com/id/1289383957/photo/blurred-bangkok-city-night-background.jpg?s=612x612&w=0&k=20&c=703HmMWVVGZEzIidIjBe71s2btnHmSX1uJABxhkGaZs=') center center / cover no-repeat fixed; position:relative;">
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>">ConsultEASE</a>
    </div>
  </nav>
  <div class="container d-flex align-items-center justify-content-center" style="min-height:70vh; position:relative; z-index:2;">
    <div class="card p-4 shadow" style="min-width:370px;">
      <h3 class="mb-3 text-center">Sign In</h3>
      <form id="loginForm" action="<?= BASE_URL ?>api/auth.php?action=login" method="post" novalidate autocomplete="off">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
          <div class="invalid-feedback" id="emailError"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
          <div class="invalid-feedback" id="passwordError"></div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
      </form>
      <div class="text-center mt-3">
        <span>Don't have an account?</span> <a href="register.php" class="btn btn-outline-primary btn-sm ms-2">Register here</a>
        <div class="mt-3">
          <a href="index.php" class="btn btn-primary">Back to Home</a>
        </div>
      </div>
    </div>
  </div>
  <div style="position:fixed;inset:0;z-index:1;background:rgba(0,20,34,0.2);"></div>
</body>
</html>