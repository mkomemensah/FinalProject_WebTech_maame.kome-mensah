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
  <style>
     
    .section-bg {
      background: #f5f7fa;
      border-radius: 18px;
      padding: 2.5rem 0;
      margin-bottom: 2rem;
    }
    .stat {color:#003A6C;font-size:2rem;font-weight:bold;}
    .feature-icon {font-size:2.5rem;color:#0070B8; margin-bottom:0.5rem;}
    .rounded-img {border-radius:18px; max-width:100%; height:auto;}
  </style>
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

  <!-- HERO SECTION -->
  <section class="container hero text-center mb-5">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="display-4 mb-3" style="color:#003A6C">Unlock Business Potential, Together.</h1>
        <p class="lead mb-4" style="color:#0070B8">Connecting you with expert consultants for every business challenge.<br>Book. Collaborate. Succeed.</p>
        <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-lg me-2">Start Now</a>
        <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-primary btn-lg">Sign In</a>
      </div>
      <div class="col-lg-5">
        <img src="https://via.placeholder.com/440x300?text=ConsultEASE+Hero" class="rounded-img shadow" alt="Hero image placeholder">
      </div>
    </div>
  </section>

  <!-- FEATURES / WHY CHOOSE US -->
  <section class="container section-bg text-center">
    <div class="row mb-4">
      <div class="col">
        <h2 style="color:#003A6C;">Why Choose ConsultEASE?</h2>
      </div>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-12 col-md-4">
        <div class="feature-icon mb-2"><i class="bi bi-shield-check"></i></div>
        <h5 class="mb-2">Verified Consultants</h5>
        <p>All professionals undergo strict background & expertise checks for your peace of mind.</p>
      </div>
      <div class="col-12 col-md-4">
        <div class="feature-icon mb-2"><i class="bi bi-clock-history"></i></div>
        <h5 class="mb-2">On-Demand Sessions</h5>
        <p>Schedule consults flexibly – whenever your business needs expert advice.</p>
      </div>
      <div class="col-12 col-md-4">
        <div class="feature-icon mb-2"><i class="bi bi-lock"></i></div>
        <h5 class="mb-2">Confidential & Secure</h5>
        <p>Your data and business details are protected by robust security measures.</p>
      </div>
    </div>
  </section>

  <!-- STATS / IMPACT SECTION -->
  <section class="container section-bg text-center">
    <div class="row g-3">
      <div class="col-6 col-md-3">
        <div class="stat">1.5k+</div>
        <div>Consultations Delivered</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat">250+</div>
        <div>Verified Consultants</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat">98%</div>
        <div>Avg. Satisfaction Rate</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat">24/7</div>
        <div>Book Anytime</div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section class="container section-bg">
    <div class="row align-items-center">
      <div class="col-lg-5">
        <img src="https://via.placeholder.com/350x300?text=About+Placeholder" class="rounded-img shadow" alt="About image placeholder">
      </div>
      <div class="col-lg-7">
        <h3 style="color:#003A6C;">About ConsultEASE</h3>
        <p class="lead" style="color:#0070B8;">Your partner in finding solutions for your business aspirations.</p>
        <ul>
          <li>Expert-matching in real-time</li>
          <li>Transparent pricing & easy payments</li>
          <li>Progress tracking with every engagement</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS SECTION -->
  <section class="container section-bg text-center">
    <h4 class="mb-4" style="color:#003A6C;">Stories from Our Clients</h4>
    <div class="row justify-content-center g-4">
      <div class="col-12 col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <img src="https://via.placeholder.com/80?text=User" class="mb-3" alt="User A" style="border-radius:40px;">
            <blockquote class="blockquote mb-0">
              <p>“ConsultEASE made it easy to connect with a top strategist for our retail launch!”</p>
              <footer class="blockquote-footer">Michelle, Retail Owner</footer>
            </blockquote>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <img src="https://via.placeholder.com/80?text=User" class="mb-3" alt="User B" style="border-radius:40px;">
            <blockquote class="blockquote mb-0">
              <p>“Our HR improvements were fast-tracked thanks to great expert support.”</p>
              <footer class="blockquote-footer">Kwame, HR Manager</footer>
            </blockquote>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <img src="https://via.placeholder.com/80?text=User" class="mb-3" alt="User C" style="border-radius:40px;">
            <blockquote class="blockquote mb-0">
              <p>“I loved the easy scheduling and how private everything stayed!”</p>
              <footer class="blockquote-footer">Aditya, Entrepreneur</footer>
            </blockquote>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TEAM SECTION (Optional) -->
  <section class="container section-bg text-center">
    <h4 class="mb-4" style="color:#003A6C;">Meet Our Core Team</h4>
    <div class="row justify-content-center g-4">
      <div class="col-12 col-md-4">
        <img src="https://via.placeholder.com/130x130?text=Team+Member" alt="Team Photo" class="mb-2" style="border-radius:80px;">
        <div class="fw-bold">Jane Doe</div>
        <div class="text-muted">Lead Consultant</div>
      </div>
      <div class="col-12 col-md-4">
        <img src="https://via.placeholder.com/130x130?text=Team+Member" alt="Team Photo" class="mb-2" style="border-radius:80px;">
        <div class="fw-bold">Peter Mensah</div>
        <div class="text-muted">Strategy Lead</div>
      </div>
      <div class="col-12 col-md-4">
        <img src="https://via.placeholder.com/130x130?text=Team+Member" alt="Team Photo" class="mb-2" style="border-radius:80px;">
        <div class="fw-bold">Linda Owusu</div>
        <div class="text-muted">Client Support</div>
      </div>
    </div>
  </section>

  <!-- CALL TO ACTION -->
  <section class="container py-4 text-center">
    <h4 style="color:#003A6C;">Ready to See the Difference?</h4>
    <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-lg">Join ConsultEASE Now</a>
  </section>

  <footer class="mt-5">
    © ConsultEASE <?= date('Y') ?> | All Rights Reserved.
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
</body>
</html>