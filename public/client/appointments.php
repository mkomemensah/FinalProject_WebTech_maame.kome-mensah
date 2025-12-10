<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>My Appointments | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
body {
  background: url('https://images.pexels.com/photos/18911016/pexels-photo-18911016/free-photo-of-an-alarm-clock-on-a-white-background.jpeg') center center / cover no-repeat fixed;
  position:relative;
}
.appt-card{border-radius:16px;box-shadow:0 4px 20px #003a6c26;margin-bottom:22px;background:#fff !important;border:1.3px solid #e1e8f0;}
.badge-pending{background:#ffa726;color:#222;font-weight:700;}
.badge-confirmed{background:#e8f5e9;color:#146c43;font-weight:700;border:1px solid #4cc790;}
.badge-completed{background:#e3f2fd;color:#1266aa;font-weight:700;border:1px solid #2196f3;}
.badge-cancelled{background:#ffebee;color:#c62828;font-weight:700;border:1px solid #e57373;}
.appt-card, .appt-card .card-body, .appt-card .small, .appt-card .text-muted, .appt-card .text-secondary {color: #181a20 !important;background:#fff !important;}
.appt-card b,.appt-card strong,.appt-card h2{color:#003a6c; font-weight: bold;}
.appt-img{width:50px;height:50px;border-radius:50%;object-fit:cover;box-shadow:0 1px 3px #8883;}
h2,h5,b,strong{color:#003a6c;}
.feedback-btn {margin-left:12px;margin-top:3px;}
</style>
</head>
<body>
<div id="toast-wrap" style="position:fixed;right:23px;bottom:32px;z-index:9999;min-width:220px;"></div>
<div id="msg"></div>
<div class="container py-4">
  <h2 style="color:#003A6C;">My Appointments</h2>
  <div id="appointments-list" class="row"></div>
  <a href="dashboard.php" class="btn btn-outline-primary mt-4">Back to Dashboard</a>
</div>
<div id="feedback-modal" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Your Feedback</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <form id="feedback-form">
        <input type="hidden" name="appointment_id" id="fb-appointment-id">
        <div class="mb-3">
          <label class="form-label">Feedback to Consultant</label>
          <textarea name="client_notes" id="fb-client-notes" class="form-control" rows="4" required maxlength="600"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
      </form>
      <div id="fb-success" class="mt-3" style="display:none;"></div>
    </div>
  </div></div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function statusBadge(status) {
  switch(status) {
    case 'pending': return 'badge badge-pending';
    case 'confirmed': return 'badge badge-confirmed';
    case 'completed': return 'badge badge-completed';
    case 'cancelled': return 'badge badge-cancelled';
    default: return 'badge';
  }
}
$(function() {
  $.getJSON('../api/appointments.php?action=list', function(appts) {
    // Toast client notifications for acceptance/rejection (one time per status change)
    let shown=false;
    appts.forEach(a=>{
      const localFlagKey = 'appt_notify_'+a.appointment_id+"_"+a.status;
      if((a.status==="confirmed"||a.status==="cancelled")&&!localStorage.getItem(localFlagKey)&&!shown){
        showToast((a.status==='confirmed'?`Your appointment for ${a.date} ${a.start_time} was <b>ACCEPTED</b>!`:`Your appointment for ${a.date} ${a.start_time} was <b>REJECTED</b>. Please book another time.`), a.status==='confirmed');
        localStorage.setItem(localFlagKey,"1");
        shown=true;
      }
    });
    let html = '';
    const now = new Date();
    appts.sort((a, b) => (b.date + b.start_time).localeCompare(a.date + a.start_time));
    let hasUpcoming = false, hasPast = false;
    for (const a of appts) {
      const apptDate = new Date(a.date + 'T' + a.start_time);
      let isUpcoming = apptDate >= now;
      const badge = statusBadge(a.status);
      // Use consultant's pic if available, else fallback
      let img = a.pic ? a.pic : '../assets/images/default-avatar.png';
      if (isUpcoming && !hasUpcoming) {html += '<h5 class="mt-4 mb-2">Upcoming</h5>'; hasUpcoming = true;}
      if (!isUpcoming && !hasPast) {html += '<h5 class="mt-4 mb-2">Past</h5>'; hasPast = true;}
      html += `<div class="col-md-7"><div class="card appt-card"><div class="card-body d-flex align-items-center">
          <img src="${img}" class="appt-img me-3">
          <div><div><b>${a.consultant_name}</b> <span class="${badge} ms-1">${a.status.charAt(0).toUpperCase()+a.status.slice(1)}</span></div>
          <div class="small text-muted">Date: ${a.date} | Time: ${a.start_time} - ${a.end_time}</div>
          <div class="small text-secondary">${a.status==='pending' ? 'Request pending confirmation' : ''}</div>
          ${a.status==='completed'?(function(){let fb='';if(a.client_notes)fb+=`<div class='mt-2'><span class='text-primary small'>Your Feedback:</span><div class='border rounded px-2 py-1 mb-1 text-secondary small'>${a.client_notes}</div></div>`;if(a.consultant_notes)fb+=`<div class='mt-2'><span class='text-success small'>Consultant Feedback:</span><div class='border rounded px-2 py-1 text-secondary small'>${a.consultant_notes}</div></div>`;return fb;})():''}
          </div></div></div></div>`;
    }
    if (!hasUpcoming && !hasPast) html += '<div class="text-muted">No appointments found.</div>';
    $('#appointments-list').html(html);
    // Feedback modal logic
    $('.feedback-btn').off('click').on('click', function(){
      const apptId = $(this).data('appt');
      const a = appts.find(a=>a.appointment_id==apptId);
      $('#fb-appointment-id').val(apptId);
      $('#fb-client-notes').val(a && a.client_notes?a.client_notes:'');
      $('#fb-success').hide();
      // Bootstrap 5 way:
      var modal = new bootstrap.Modal(document.getElementById('feedback-modal'));
      modal.show();
    });
  });
});
$('#feedback-form').off('submit').on('submit', function(e){
  e.preventDefault();
  var data = $(this).serialize();
  $.post('../api/feedback.php?action=submit', data, function(resp) {
    if(resp.success) {
      $('#fb-success').html("<div class='alert alert-success'>Feedback submitted!</div>").show();
      showToast('Feedback submitted!', true);
      setTimeout(()=>{ 
        var modal = bootstrap.Modal.getInstance(document.getElementById('feedback-modal'));
        if(modal) modal.hide();
        $('.modal-backdrop').remove(); 
        location.reload();
      }, 1000);
    } else {
      $('#fb-success').html("<div class='alert alert-danger'>"+(resp.error||"Error")+"</div>").show();
      showToast(resp.error||'Failed to submit feedback', false);
    }
  },'json');
});
function showToast(msg, positive) {
  var color = (positive === false) ? 'alert-danger' : (positive === true ? 'alert-success' : 'alert-secondary');
  var toast = $('<div class="alert '+color+' py-2 px-3 mb-2 shadow"><b>'+msg+'</b></div>');
  $('#toast-wrap').append(toast);
  setTimeout(function(){ toast.fadeOut(350, function(){ toast.remove(); }); },1800);
}
</script>
</body>
</html>
