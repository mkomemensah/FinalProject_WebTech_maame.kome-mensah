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
      <div class="col-lg-7 d-flex flex-column justify-content-center align-items-center align-items-lg-start text-center text-lg-start px-4 px-md-5 py-5" style="min-height:320px;">
        <h1 class="display-4 mb-3" style="color:#003A6C">Unlock Business Potential, Together.</h1>
        <p class="lead mb-4" style="color:#0070B8">Connecting you with expert consultants for every business challenge.<br>Book. Collaborate. Succeed.</p>
        <div>
          <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-lg me-2">Start Now</a>
          <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-primary btn-lg">Sign In</a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block position-relative p-0" style="min-height:320px;">
        <div style="position:absolute; inset:0; background:url('https://it360.co.nz/wp-content/uploads/2024/10/consulting.jpg') center center / cover no-repeat; border-radius:18px;"></div>
        <div style="position:absolute; inset:0; background:rgba(0,58,108,0.34); border-radius:18px;"></div>
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
      <div class="col-lg-5 mb-3 mb-lg-0">
        <img src="https://www.ache.org/-/media/ache/blog/internal_culture_1024x425/blogheader_consultant-01.png?h=535&w=1290&la=en&hash=88F960F791607C9129E04E98F0999BDC" class="rounded-img shadow" alt="Consultant Banner" style="width:100%;max-width:410px;height:auto;">
      </div>
      <div class="col-lg-7">
        <h3 style="color:#003A6C;">About ConsultEASE</h3>
        <p class="lead" style="color:#0070B8;">
          ConsultEASE is your trusted platform for connecting with top business consultants across industries. We bring together vetted professionals and innovative businesses to solve challenges, drive growth, and deliver results you can see. Our marketplace simplifies expert-matching, ensures transparency in every engagement, and keeps your business data secure. 
        </p>
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
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXEkahlhDPkcLBTwrmh_XHbBkm0KLCW8Chzw&s" class="mb-3" alt="Michael" style="border-radius:40px;width:80px;height:80px;object-fit:cover;">
            <blockquote class="blockquote mb-0">
              <p>“ConsultEASE made it easy to connect with a top strategist for our retail launch!”</p>
              <footer class="blockquote-footer">Michael</footer>
            </blockquote>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <img src="https://www.waldenu.edu/media/5058/seo-1630-bs-portrait-of-pretty-african-ame-362193577-1200x675" class="mb-3" alt="Ama" style="border-radius:40px;width:80px;height:80px;object-fit:cover;">
            <blockquote class="blockquote mb-0">
              <p>“Our HR improvements were fast-tracked thanks to great expert support.”</p>
              <footer class="blockquote-footer">Ama</footer>
            </blockquote>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <img src="https://unza.ecampus.edu.zm/wp-content/uploads/2020/09/Is-an-MBA-in-Human-Resource-Management-a-Good-Option-600x430.jpg" class="mb-3" alt="Boatemaa" style="border-radius:40px;width:80px;height:80px;object-fit:cover;">
            <blockquote class="blockquote mb-0">
              <p>“I loved the easy scheduling and how private everything stayed!”</p>
              <footer class="blockquote-footer">Boatemaa</footer>
            </blockquote>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- REACH OUT SECTION -->
  <section class="container section-bg my-5">
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-6 d-flex flex-column justify-content-center h-100 mb-4 mb-lg-0 px-4 px-lg-5" style="min-height:350px;">
        <h2 class="mb-2" style="color:#003A6C;font-weight:700;">Reach out</h2>
        <div class="mb-3 mt-2" style="height:3px;width:55px;background:#0070B8;border-radius:3px;"></div>
        <p class="mb-3" style="color:#222;opacity:0.85;">Questions about onboarding, partnerships, or business consulting? Leave us a note and we'll respond within one business day.</p>
        <div class="mb-1"><strong>Email:</strong> <span style="color:#0070B8;">consultease@gmail.com</span></div>
        <div class="mb-1"><strong>Phone:</strong> <span style="color:#0070B8;">+233 555 123 456</span></div>
        <div class="mb-1"><strong>Head Office:</strong> 1 University Avenue, Berekuso</div>
        <div class="mb-1"><strong>Security hotline:</strong> <span style="color:#0070B8;">consultease@gmail.com</span> <span class="badge bg-light text-dark border ms-2">24/7 • 1 hr SLA</span></div>
      </div>
      <div class="col-lg-6">
        <?php
        $contact_success = "";
        $contact_error = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
          $name = trim($_POST['name'] ?? '');
          $email = trim($_POST['email'] ?? '');
          $role = trim($_POST['role'] ?? '');
          $msg = trim($_POST['message'] ?? '');
          if (!$name || !$email || !$msg) {
            $contact_error = "All fields are required.";
          } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contact_error = "Enter a valid email address.";
          } else {
            // Prepare email (simple mail)
            $to = 'consultease@gmail.com';
            $subject = "Contact Form Submission: $name ($role)";
            $body = "Name: $name\nEmail: $email\nRole: $role\nMessage:\n$msg";
            $headers = "From: $email\r\nReply-To: $email";
            if (@mail($to, $subject, $body, $headers)) {
              $contact_success = "Thank you! Your message has been sent.";
            } else {
              $contact_error = "Sorry, something went wrong. Please try again later.";
            }
          }
        }
        ?>
        <?php if($contact_success): ?>
          <div class="alert alert-success"> <?= htmlspecialchars($contact_success) ?> </div>
        <?php elseif($contact_error): ?>
          <div class="alert alert-danger"> <?= htmlspecialchars($contact_error) ?> </div>
        <?php endif; ?>
        <form method="post" class="bg-light p-4 rounded shadow-sm">
          <div class="mb-3 text-start">
            <label class="form-label">Name</label>
            <input required name="name" class="form-control" type="text" placeholder="Your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Email</label>
            <input required name="email" class="form-control" type="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">I am a</label>
            <select name="role" class="form-select">
              <option value="" disabled selected>Select your role</option>
              <option value="Client" <?= (($_POST['role'] ?? '')=='Client'?'selected':'') ?>>Client</option>
              <option value="Consultant" <?= (($_POST['role'] ?? '')=='Consultant'?'selected':'') ?>>Consultant</option>
              <option value="Partner" <?= (($_POST['role'] ?? '')=='Partner'?'selected':'') ?>>Partner</option>
              <option value="Other" <?= (($_POST['role'] ?? '')=='Other'?'selected':'') ?>>Other</option>
            </select>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Message</label>
            <textarea required name="message" class="form-control" rows="3" placeholder="How can we help?"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" name="contact_submit" class="btn btn-primary btn-lg w-100">Submit</button>
        </form>
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