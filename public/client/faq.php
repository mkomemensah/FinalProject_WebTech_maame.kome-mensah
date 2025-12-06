<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Support & FAQ | ConsultEASE</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background: url('https://www.shutterstock.com/image-photo/design-workplace-laptop-cup-coffee-600nw-639194614.jpg') center center / cover no-repeat fixed; position:relative;">
<div style="position:fixed;inset:0;z-index:1;background:
    linear-gradient(120deg, rgba(0,112,184,0.23) 0%, rgba(255,255,255,0.72) 80%);
"></div>
<div class="container py-4" style="max-width:720px; position:relative; z-index:2;">
  <h2 class="mb-4" style="color:#003A6C;font-size:2.45rem;font-weight:800;text-shadow:0 2px 10px #fff5;letter-spacing:0.5px;">Frequently Asked Questions</h2>
  <div class="accordion" id="faqAccordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="q1"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1" aria-expanded="true" aria-controls="c1">How do I find and book a consultant?</button></h2>
      <div id="c1" class="accordion-collapse collapse show" aria-labelledby="q1" data-bs-parent="#faqAccordion">
        <div class="accordion-body">Use the "Find a Consultant" tab to browse or filter consultants by expertise. Click "Book" next to your chosen consultant to select a date/time and send a session request.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="q2"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2" aria-expanded="false" aria-controls="c2">How are appointments confirmed?</button></h2>
      <div id="c2" class="accordion-collapse collapse" aria-labelledby="q2" data-bs-parent="#faqAccordion">
        <div class="accordion-body">After you send a booking request, the consultant reviews and confirms or suggests a new time. You will receive a notification once confirmed.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="q3"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3" aria-expanded="false" aria-controls="c3">Can I reschedule or cancel a booking?</button></h2>
      <div id="c3" class="accordion-collapse collapse" aria-labelledby="q3" data-bs-parent="#faqAccordion">
        <div class="accordion-body">Yes! Go to "My Appointments" to see your bookings. There you can request to reschedule or cancel, subject to each consultant’s policy.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="q4"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c4" aria-expanded="false" aria-controls="c4">How are consultants selected and vetted?</button></h2>
      <div id="c4" class="accordion-collapse collapse" aria-labelledby="q4" data-bs-parent="#faqAccordion">
        <div class="accordion-body">All consultants are carefully screened for qualifications and expertise before joining ConsultEASE. You can read about their background by clicking their name in the consultant finder.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="q5"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c5" aria-expanded="false" aria-controls="c5">Is my privacy protected?</button></h2>
      <div id="c5" class="accordion-collapse collapse" aria-labelledby="q5" data-bs-parent="#faqAccordion">
        <div class="accordion-body">Yes. Your information and session details are private and accessible only to you and your booked consultants. Please see our privacy policy for more details.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="q6"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c6" aria-expanded="false" aria-controls="c6">Still have questions?</button></h2>
      <div id="c6" class="accordion-collapse collapse" aria-labelledby="q6" data-bs-parent="#faqAccordion">
        <div class="accordion-body">Contact our support team anytime at <a href="mailto:consultease@gmail.com">consultease@gmail.com</a>.</div>
      </div>
    </div>
  </div>
  <a href="dashboard.php" class="btn btn-primary btn-lg fw-bold shadow-sm px-5 py-3 mt-5" style="font-size:1.25rem;">Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
