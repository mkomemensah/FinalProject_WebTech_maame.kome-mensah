<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Appointments | ConsultEASE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: url('https://images.pexels.com/photos/18911016/pexels-photo-18911016/free-photo-of-an-alarm-clock-on-a-white-background.jpeg') center center / cover no-repeat fixed;
            position: relative;
            min-height: 100vh;
        }
        
        .appt-card {
            border-radius: 16px;
            box-shadow: 0 4px 20px #003a6c26;
            margin-bottom: 22px;
            background: #fff !important;
            border: 1.3px solid #e1e8f0;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .appt-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 58, 108, 0.2);
        }
        
        .badge-pending { background: #ffa726; color: #222; font-weight: 700; }
        .badge-confirmed { background: #e8f5e9; color: #146c43; font-weight: 700; border: 1px solid #4cc790; }
        .badge-completed { background: #e3f2fd; color: #1266aa; font-weight: 700; border: 1px solid #2196f3; }
        .badge-cancelled { background: #ffebee; color: #c62828; font-weight: 700; border: 1px solid #e57373; }
        
        .appt-card, .appt-card .card-body, .appt-card .small, .appt-card .text-muted, .appt-card .text-secondary { 
            color: #181a20 !important;
            background: #fff !important;
        }
        
        .appt-card b, .appt-card strong, .appt-card h2 { 
            color: #003a6c; 
            font-weight: bold; 
        }
        
        .appt-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 2px solid #fff;
        }
        
        h2, h5, b, strong { 
            color: #003a6c; 
        }
        
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .appointment-details {
            border-left: 3px solid #0d6efd;
            padding-left: 15px;
            margin: 10px 0;
        }
        
        .no-appointments {
            text-align: center;
            padding: 50px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .no-appointments i {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        
        .appointment-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .appointment-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .appointment-consultant {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .appointment-consultant img {
            margin-right: 10px;
        }
        
        .appointment-consultant-info h6 {
            margin: 0;
            font-weight: 600;
        }
        
        .appointment-consultant-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .appointment-card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .appointment-time {
                margin-top: 5px;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
<div id="toast-wrap" style="position:fixed;right:23px;bottom:32px;z-index:9999;min-width:220px;"></div>
<div id="alerts-container"></div>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0" style="color:#003A6C;">
            <i class="bi bi-calendar-check me-2"></i>My Appointments
        </h1>
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Appointment Management</h5>
                            <p class="text-muted mb-0">View, reschedule, or cancel your appointments</p>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" id="filter-all">All</button>
                            <button type="button" class="btn btn-outline-primary" id="filter-upcoming">Upcoming</button>
                            <button type="button" class="btn btn-outline-primary" id="filter-past">Past</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="appointments-list" class="row">
        <!-- Appointments will be loaded here via JavaScript -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading your appointments...</p>
        </div>
    </div>
    
    <div id="no-appointments" class="no-appointments d-none">
        <i class="bi bi-calendar-x"></i>
        <h4>No Appointments Found</h4>
        <p class="text-muted">You don't have any appointments scheduled yet.</p>
        <a href="consultants.php" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle me-1"></i> Book an Appointment
        </a>
    </div>
</div>
<?php include __DIR__ . '/../includes/appointment-modals.php'; ?>
<div id="feedback-modal" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white">
      <h5 class="modal-title">
        <i class="bi bi-chat-square-text me-2"></i>Your Feedback
      </h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <form id="feedback-form">
        <input type="hidden" name="appointment_id" id="fb-appointment-id">
        <div class="mb-3">
          <label class="form-label">How was your consultation experience?</label>
          <div class="mb-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="rating-5" value="5" required>
              <label class="form-check-label" for="rating-5">
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="rating-4" value="4">
              <label class="form-check-label" for="rating-4">
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star text-warning"></i>
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="rating-3" value="3">
              <label class="form-check-label" for="rating-3">
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star text-warning"></i>
                <i class="bi bi-star text-warning"></i>
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="rating-2" value="2">
              <label class="form-check-label" for="rating-2">
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star text-warning"></i>
                <i class="bi bi-star text-warning"></i>
                <i class="bi bi-star text-warning"></i>
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="rating-1" value="1">
              <label class="form-check-label" for="rating-1">
                <i class="bi bi-star-fill text-warning"></i>
                <i class="bi bi-star text-warning"></i>
                <i class="bi bi-star text-warning"></i>
                <i class="bi bi-star text-warning"></i>
                <i class="bi bi-star text-warning"></i>
              </label>
            </div>
          </div>
          <label for="fb-client-notes" class="form-label">Share your feedback (optional)</label>
          <textarea name="client_notes" id="fb-client-notes" class="form-control" rows="4" placeholder="What did you like or what could be improved?" maxlength="600"></textarea>
          <div class="form-text">Your feedback helps us improve our service.</div>
        </div>
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send-fill me-1"></i> Submit Feedback
          </button>
        </div>
      </form>
    </div>
  </div></div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom JavaScript -->
<script src="../assets/js/appointment-management.js"></script>

<script>
// Status badge helper function
function statusBadge(status) {
  switch(status) {
    case 'pending': return 'badge bg-warning text-dark';
    case 'confirmed': return 'badge bg-success';
    case 'completed': return 'badge bg-primary';
    case 'cancelled': return 'badge bg-danger';
    default: return 'badge bg-secondary';
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
      // Use consultant's pic if available, else use placeholder
      let img = a.pic ? a.pic : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(a.consultant_name) + '&background=003A6C&color=fff&size=100';
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
