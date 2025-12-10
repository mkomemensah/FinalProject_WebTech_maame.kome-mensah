<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background: url('https://img.freepik.com/premium-photo/reading-books-online-open-book-laptop-blue-background-3d-render_407474-4746.jpg') center center / cover no-repeat fixed; position:relative;">
<div style="position:fixed;inset:0;z-index:1;background:rgba(255,255,255,0.72);"></div>
<div class="container py-4" style="max-width:540px; position:relative; z-index:2;">
<h2 class="mb-3" style="color:#003A6C;">Book a Consultation</h2>
<div id="msg"></div>
<form id="booking-form" class="card p-4 shadow-sm" autocomplete="off">
  <div class="mb-3">
    <label class="form-label">Consultant *</label>
    <select id="consultant-select" class="form-select" required name="consultant_id"></select>
  </div>
  <div id="manual-row" class="mb-3 row g-2 align-items-center" style="display:block">
    <div class="col-6">
      <label class="form-label">Date *</label>
      <input name="date" id="date-input" type="date" class="form-control">
    </div>
    <div class="col-3">
      <label class="form-label">Start *</label>
      <input name="start_time" id="start-time-input" type="time" class="form-control">
    </div>
    <div class="col-3">
      <label class="form-label">End *</label>
      <input name="end_time" id="end-time-input" type="time" class="form-control">
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">Meeting Notes (optional)</label>
    <textarea name="notes" class="form-control" rows="2" maxlength="255"></textarea>
  </div>
  <button type="submit" class="btn btn-primary w-100">Send Booking Request</button>
</form>
<a href="dashboard.php" class="btn btn-outline-primary mt-3">Back to Dashboard</a>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let consultants = [];
$('#consultant-select').html('<option>Loading consultants...</option>');

// Load consultants
$.getJSON('../api/consultants.php?action=list', function(list){
  consultants = list;
  if (!consultants.length) {
    $('#consultant-select').html('<option>No consultants available</option>');
    return;
  }
  $('#consultant-select').html('<option value="">Select consultant...</option>' + 
    consultants.map(c=>`<option value="${c.consultant_id}">${c.name} (${c.expertise||''})</option>`).join(''));
});

function setDateMinToday() {
  var today = (new Date()).toISOString().split('T')[0];
  $('#date-input').attr('min', today);
}
setDateMinToday();
$('#date-input').on('focus', setDateMinToday);

// Validate and submit
$('#booking-form').off('submit').on('submit', function(e){
  e.preventDefault();
  const date = $('#date-input').val();
  const start = $('#start-time-input').val();
  const end = $('#end-time-input').val();
  // Accurate Ghana (GMT) time regardless of user's timezone
  const accraNow = new Date(new Date().toLocaleString('en-US', { timeZone: 'Africa/Accra' }));
  const todayGhana = accraNow.toISOString().split('T')[0];
  if (!date || !start || !end) {
    $('#msg').html('<div class="alert alert-danger">Missing required fields.</div>');
    return;
  }
  if (date < todayGhana) {
    $('#msg').html('<div class="alert alert-danger">Cannot book a date before today.</div>');
    return;
  }
  if (date === todayGhana) {
    // Use Date objects for comparison
    const userDateTime = new Date(`${date}T${start}`);
    if (userDateTime <= accraNow) {
      $('#msg').html('<div class="alert alert-danger">Choose a start time later than now (Ghana/GMT).</div>');
      return;
    }
  }
  if (end <= start) {
    $('#msg').html('<div class="alert alert-danger">End time must be after start time.</div>');
    return;
  }
  const data = $(this).serialize();
  $.post('../api/appointments.php?action=book', data, function(resp){
    if(resp.success) {
      $('#msg').html('<div class="alert alert-success">Booking request sent to consultant for confirmation!</div>');
      $('#booking-form')[0].reset();
      $('#manual-row').show();
    } else {
      $('#msg').html('<div class="alert alert-danger">'+(resp.error||'Booking failed')+'</div>');
    }
  },'json');
});
</script>
</body>
</html>