<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointments | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
    .badge-warning { background: #ffa726!important; color:#222!important; font-weight:700; }
    .badge-success { background: #e8f5e9!important; color: #146c43!important; font-weight:700; border:1px solid #4cc790; }
    .badge-info { background: #e3f2fd!important; color:#1266aa!important; font-weight:700; border:1px solid #2196f3; }
    .badge-danger { background: #ffebee!important; color: #c62828!important; font-weight:700; border:1px solid #e57373; }
    .feedback-btn {margin-left:8px;margin-top:2px;}
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Upcoming Appointments</h2>
    <table class="table table-striped table-bordered shadow-sm">
        <thead>
            <tr>
                <th>Client</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Business Problem</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="appts-table"></tbody>
    </table>
</div>
<div id="feedback-modal" class="modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Consultant Feedback</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <form id="feedback-form">
        <input type="hidden" name="appointment_id" id="fb-appointment-id">
        <div class="mb-3">
          <label class="form-label">Notes about this session</label>
          <textarea name="consultant_notes" id="fb-consultant-notes" class="form-control" rows="4" required maxlength="600"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
      </form>
      <div id="fb-success" class="mt-3" style="display:none;"></div>
    </div>
  </div></div>
</div>
<div class="modal fade" id="problemModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Business Problem</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="problemModalBody"></div></div></div></div>
<div class="modal fade" id="detailsModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Session Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="detailsModalBody"></div></div></div></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function statusBadge(status) {
  switch(status) {
    case 'pending': return 'badge badge-warning';
    case 'confirmed': return 'badge badge-success';
    case 'completed': return 'badge badge-info';
    case 'cancelled': return 'badge badge-danger';
    default: return 'badge';
  }
}
function fetchAppointments() {
  $.getJSON('../api/appointments.php?action=list', function(appts) {
    let html = '';
    let now = new Date();
    appts.forEach(function(a) {
      let badge = statusBadge(a.status);
      let btns = '';
      // Accept/reject for pending
      if (a.status === 'pending') {
        btns = `<button class='btn btn-outline-success btn-sm me-2 accept-appt' data-id='${a.appointment_id}'>Accept</button>`+
               `<button class='btn btn-outline-danger btn-sm reject-appt' data-id='${a.appointment_id}'>Reject</button>`;
      }
      // Manual mark as complete for confirmed that are in the past
      if (a.status === 'confirmed') {
        let endDateTime = new Date(a.date+'T'+a.end_time);
        if (endDateTime < now) {
          btns += `<button class='btn btn-outline-primary btn-sm ms-2 mark-completed' data-id='${a.appointment_id}'>Mark as Completed</button>`;
        }
      }
      // Feedback UI for completed appointments
      let feedbackUI = '';
      if (a.status === 'completed') {
        feedbackUI = '<div class="mt-2">';
        if (a.consultant_notes) {
          feedbackUI += `<span class='text-primary small'>Your Feedback:</span><div class='border rounded px-2 py-1 mb-1 text-secondary small'>${a.consultant_notes}</div>`;
        }
        if (a.client_notes) {
          feedbackUI += `<span class='text-success small'>Client Feedback:</span><div class='border rounded px-2 py-1 text-secondary small'>${a.client_notes}</div>`;
        }
        if (!a.consultant_notes) {
          feedbackUI += `<button class='btn btn-sm btn-outline-primary feedback-btn' data-appt='${a.appointment_id}'>Leave Feedback</button>`;
        } else {
          feedbackUI += `<button class='btn btn-sm btn-outline-secondary feedback-btn' data-appt='${a.appointment_id}'>Edit Feedback</button>`;
        }
        feedbackUI += '</div>';
      }
      let problem = a.business_problem || '-';
      let truncated = problem.length > 50 ? problem.substr(0, 50) + '...' : problem;
      let viewBtn = problem.length > 50 ? `<button class='btn btn-info btn-sm ms-1 view-problem' data-problem="${$('<div>').text(problem).html()}">View</button>` : '';
      let problemCell = truncated + viewBtn;
      let detailsBtn = `<button class='btn btn-secondary btn-sm ms-2 view-details' data-client="${a.client_name}" data-email="${a.email||'-'}" data-date="${a.date}" data-time="${a.start_time} - ${a.end_time}" data-problem="${$('<div>').text(problem).html()}">Details</button>`;
      let markBtn = `<button class='btn btn-outline-primary btn-sm mark-completed' data-id='${a.appointment_id}'>Mark as Completed</button>`;
      html += `<tr><td>${a.client_name}</td><td>${a.date}</td><td>${a.start_time} - ${a.end_time}</td><td><span class='${badge}'>${a.status.charAt(0).toUpperCase()+a.status.slice(1)}</span></td><td>${problemCell}</td><td>${markBtn} ${detailsBtn}</td></tr>`;
    });
    $('#appts-table').html(html);
    // Feedback modal logic (existing)
    $('.feedback-btn').off('click').on('click', function(){
      const apptId = $(this).data('appt');
      $.getJSON('../api/appointments.php?action=list', function(appts2) {
        const a = appts2.find(a=>a.appointment_id==apptId);
        $('#fb-appointment-id').val(apptId);
        $('#fb-consultant-notes').val(a && a.consultant_notes?a.consultant_notes:'');
        $('#fb-success').hide();
        $('#feedback-modal').modal('show');
      });
    });
    // Manual mark as completed handler
    $('.mark-completed').off('click').on('click', function(){
      let id = $(this).data('id');
      $(this).prop('disabled', true);
      $.post('../api/appointments.php?action=mark_completed', { appointment_id: id }, function(resp) {
        fetchAppointments();
      }, 'json');
    });
  });
}
$(document).ready(function() {
  fetchAppointments();
  // Bootstrap modal support (for Bootstrap 5 only)
  if (typeof bootstrap==='undefined'){
    $.getScript('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js');
  }
  $('#appts-table').on('click', '.accept-appt', function() {
    let id = $(this).data('id');
    $.post('../api/appointments.php?action=accept', { appointment_id: id }, function(resp) {
      fetchAppointments();
    }, 'json');
  });
  $('#appts-table').on('click', '.reject-appt', function() {
    let id = $(this).data('id');
    $.post('../api/appointments.php?action=reject', { appointment_id: id }, function(resp) {
      fetchAppointments();
    }, 'json');
  });
  $('#appts-table').on('click', '.view-problem', function(){
    $('#problemModalBody').text($(this).data('problem'));
    $('#problemModal').modal('show');
  });
  $('#appts-table').on('click', '.view-details', function(){
    const c = $(this).data('client'),
          e = $(this).data('email'),
          d = $(this).data('date'),
          t = $(this).data('time'),
          p = $(this).data('problem');
    $('#detailsModalBody').html(`<b>Client:</b> ${c}<br><b>Email:</b> ${e}<br><b>Date:</b> ${d}<br><b>Time:</b> ${t}<br><b>Business Problem:</b><br>${p}`);
    $('#detailsModal').modal('show');
  });
});
$('#feedback-form').on('submit', function(e){
  e.preventDefault();
  var data = $(this).serialize();
  $.post('../api/feedback.php?action=submit', data, function(resp) {
    if(resp.success) {
      $('#fb-success').html("<div class='alert alert-success'>Feedback submitted!</div>").show();
      setTimeout(()=>{ $('#feedback-modal').modal('hide'); $('.modal-backdrop').remove(); fetchAppointments(); }, 1000);
    } else {
      $('#fb-success').html("<div class='alert alert-danger'>"+(resp.error||"Error")+"</div>").show();
    }
  },'json');
});
</script>
</body>
</html>