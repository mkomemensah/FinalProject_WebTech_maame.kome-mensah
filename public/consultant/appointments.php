<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
?><!DOCTYPE html>
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
    #appt-list { margin-top: 2rem; }
    .appt-card.card {
      border-radius: 16px; 
      border: none; 
      background: #f0f6fb; /* light Deloitte blue */
      transition: box-shadow 0.2s, transform 0.2s;
      box-shadow: 0 6px 32px rgba(0,58,108,.09);
    }
    .appt-card:hover {
      box-shadow: 0 12px 36px rgba(0,58,108,.15);
      transform: translateY(-2px) scale(1.01);
    }
    .status-badge {
      border-radius: 8px; 
      font-size: 0.95rem; 
      font-weight:600; 
      padding: 2.5px 15px;
      letter-spacing:0.03em;
    }
    .status-completed { background: #e4f7e6; color: #188248; border:1px solid #4fcc88; }
    .status-confirmed { background: #e3f2fd; color:#1266aa; border:1px solid #2196f3; }
    .status-pending { background: #fff9db; color:#ee9c00; border:1px solid #fbc02d; }
    .status-cancelled { background: #ffebee; color: #c62828; border:1px solid #e57373; }
    .btn-brand {
      border-radius: 2rem;
      font-weight: 600;
      box-shadow:0 2px 8px #003A6C11;
      transition: background .17s, color .17s, box-shadow .17s;
      letter-spacing: 0.03em;
    }
    .btn-brand.feedback {  background:#fff; color: #003A6C; border:2px solid #003A6C; }
    .btn-brand.feedback:hover { background:#003A6C; color: #fff; }
    .btn-brand.details { background:#2196f3; color:#fff; border:none; }
    .btn-brand.details:hover { background:#176ca0; color:#fff; }
    .appt-card .btn + .btn { margin-left: 7px;}
    .feedback-btn, .view-details { min-width:110px; margin:4px 3px 0 0; }
    @media (max-width: 767px) { #appt-list .col-md-6, #appt-list .col-lg-4 { flex:0 0 100%; max-width:100%; } }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">Appointments</h2>
    <div id="appt-list" class="row gx-4 gy-4"></div>
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
function getStatusBadge(status) {
  let map = {
    'completed': 'status-badge status-completed',
    'confirmed': 'status-badge status-confirmed',
    'pending': 'status-badge status-pending',
    'cancelled': 'status-badge status-cancelled',
  };
  return map[status]||'status-badge';
}
function fetchAppointments() {
  console.log('Fetching appointments...');
  $.ajax({
    url: '../api/appointments.php?action=list',
    dataType: 'json',
    success: function(appts) {
      console.log('Appointments API response:', appts);
      let html = '';
      
      if (!appts || !Array.isArray(appts)) {
        console.error('Invalid appointments data received:', appts);
        html = `<div class='col-12'><div class='alert alert-danger'>Error loading appointments. Please try again later.</div></div>`;
      } else if (appts.length === 0) {
        console.log('No appointments found');
        html = `<div class='col-12'><div class='text-center py-5'><img src='https://cdn-icons-png.flaticon.com/512/4076/4076549.png' alt='No Appointments' width='80' class='mb-2'/><h5 class='text-secondary'>No appointments found</h5></div></div>`;
      } else {
        console.log('Rendering', appts.length, 'appointments');
        appts.forEach(function(a, index) {
          console.log('Appointment', index, ':', a);
          let badgeClass = getStatusBadge(a.status);
          let actionBtns = '';
          if (a.status === 'pending') {
            actionBtns = `
              <button class='btn btn-success btn-sm accept-appointment me-2' data-appointment-id='${a.appointment_id}'>
                <i class='bi bi-check-circle'></i> Accept
              </button>
              <button class='btn btn-outline-danger btn-sm reject-appointment me-2' data-appointment-id='${a.appointment_id}'>
                <i class='bi bi-x-circle'></i> Reject
              </button>
            `;
          } else if (a.status === 'completed') {
            actionBtns = `<button class='btn btn-brand feedback btn-sm feedback-btn me-2' data-appointment-id='${a.appointment_id}'><i class="bi bi-chat-left-text"></i> Give Feedback</button>`;
          }
          let detailsBtn = `<button class='btn btn-brand details btn-sm view-details' data-client='${a.client_name||''}' data-email='${a.client_email||''}' data-date='${a.date}' data-time='${a.start_time} - ${a.end_time}' data-problem='${a.business_problem||'No details available'}'><i class="bi bi-info-circle"></i> Details</button>`;
          
          html += `<div class='col-md-6 col-lg-4'><div class='appt-card card h-100 shadow-sm p-3'>
            <div class='d-flex align-items-center mb-3'>
              <div class='rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm' style='width:44px;height:44px;font-weight:bold;font-size:1.2rem;'>${a.client_name?a.client_name.charAt(0).toUpperCase():'?'}</div>
              <div class='ms-3'><div class='fw-bold mb-1'>${a.client_name||'Unknown Client'}</div><div class='small text-secondary'>${a.client_email||''}</div></div>
            </div>
            <div class='mb-2'><b>Date:</b> ${a.date || 'N/A'}&nbsp;&nbsp;<b>Time:</b> ${a.start_time || ''} - ${a.end_time || ''}</div>
            <div class='mb-2'><span class='${badgeClass}'>${a.status ? a.status.charAt(0).toUpperCase() + a.status.slice(1) : 'Unknown Status'}</span></div>
            <div class='d-flex flex-wrap align-items-center'>${actionBtns}${detailsBtn}</div>
          </div></div>`;
        });
      }
      $('#appt-list').html(html);
      
      // Set up event handlers after the HTML is inserted
      setupEventHandlers(appts);
    },
    error: function(xhr, status, error) {
      console.error('Error fetching appointments:', status, error);
      $('#appt-list').html(`<div class='col-12'><div class='alert alert-danger'>Error loading appointments: ${error || 'Unknown error'}</div></div>`);
    }
  });
}

function setupEventHandlers(appts) {
  // Handle accept appointment
  $('#appt-list').off('click', '.accept-appointment').on('click', '.accept-appointment', function(){
    const $btn = $(this);
    const appointmentId = $btn.data('appointment-id');
    
    // Show loading state
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Accepting...');
    
    // Send accept request
    $.ajax({
      url: '../api/appointments.php?action=accept',
      type: 'POST',
      data: { appointment_id: appointmentId },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          // Refresh the appointments list
          fetchAppointments();
        } else {
          alert(response.error || 'Failed to accept appointment');
          $btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Accept');
        }
      },
      error: function() {
        alert('An error occurred while processing your request');
        $btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Accept');
      }
    });
  });
  
  // Handle reject appointment
  $('#appt-list').off('click', '.reject-appointment').on('click', '.reject-appointment', function(){
    if (!confirm('Are you sure you want to reject this appointment?')) return;
    
    const $btn = $(this);
    const appointmentId = $btn.data('appointment-id');
    
    // Show loading state
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Rejecting...');
    
    // Send reject request
    $.ajax({
      url: '../api/appointments.php?action=reject',
      type: 'POST',
      data: { appointment_id: appointmentId },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          // Refresh the appointments list
          fetchAppointments();
        } else {
          alert(response.error || 'Failed to reject appointment');
          $btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i> Reject');
        }
      },
      error: function() {
        alert('An error occurred while processing your request');
        $btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i> Reject');
      }
    });
  });
  
  // Feedback button click handler with error handling
  $('#appt-list').off('click', '.feedback-btn').on('click', '.feedback-btn', function(){
    const $btn = $(this);
    const $card = $btn.closest('.appt-card');
    const idx = $card.parent().index();
    
    // Show loading state
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
    
    $.getJSON('../api/appointments.php?action=list')
      .done(function(appts2) {
        const a = appts2[idx];
        if (!a) {
          console.error('Appointment not found at index', idx);
          return;
        }
        
        $('#fb-appointment-id').val(a.appointment_id);
        $('#fb-consultant-notes').val(a.consultant_notes || '');
        $('#fb-success').hide();
        
        // Initialize and show the modal
        const feedbackModal = new bootstrap.Modal(document.getElementById('feedback-modal'));
        feedbackModal.show();
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Error loading appointment details:', textStatus, errorThrown);
        alert('Failed to load appointment details. Please try again.');
      })
      .always(function() {
        $btn.prop('disabled', false).html('<i class="bi bi-chat-left-text"></i> Give Feedback');
      });
  });
  
  // View details button handler
  $('#appt-list').off('click', '.view-details').on('click', '.view-details', function(){
    const c = $(this).data('client'),
          e = $(this).data('email'),
          d = $(this).data('date'),
          t = $(this).data('time'),
          p = $(this).data('problem');
          
    const modalContent = '<b>Client:</b> ' + (c || 'N/A') + 
                        '<br><b>Email:</b> ' + (e || '-') + 
                        '<br><b>Date:</b> ' + (d || 'N/A') + 
                        '<br><b>Time:</b> ' + (t || '') + 
                        '<br><b>Business Problem:</b><br>' + (p || '-');
    $('#detailsModalBody').html(modalContent);
    $('#detailsModal').modal('show');
  });
}
$(document).ready(function() {
  fetchAppointments();
  // Bootstrap modal support (for Bootstrap 5 only)
  if (typeof bootstrap==='undefined'){
    $.getScript('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js');
  }
  $('#appt-list').on('click', '.view-problem', function(){
    $('#problemModalBody').text($(this).data('problem'));
    $('#problemModal').modal('show');
  });
  $('#appt-list').on('click', '.view-details', function(){
    const c = $(this).data('client'),
          e = $(this).data('email'),
          d = $(this).data('date'),
          t = $(this).data('time'),
          p = $(this).data('problem');
    const modalContent = '<b>Client:</b> ' + (c || 'N/A') + 
                        '<br><b>Email:</b> ' + (e || '-') + 
                        '<br><b>Date:</b> ' + (d || 'N/A') + 
                        '<br><b>Time:</b> ' + (t || '') + 
                        '<br><b>Business Problem:</b><br>' + (p || '-');
    $('#detailsModalBody').html(modalContent);
    $('#detailsModal').modal('show');
  });
});
// Handle feedback form submission with event delegation
$(document).on('submit', '#feedback-form', function(e) {
    e.preventDefault();
    
    // Basic form validation
    const notes = $('#fb-consultant-notes').val().trim();
    if (!notes) {
        $('#fb-success').html("<div class='alert alert-warning'>Please enter your feedback notes</div>").show();
        return false;
    }
    
    var $form = $(this);
    var data = $form.serialize();
    
    // Show loading state
    var $submitBtn = $form.find('button[type="submit"]');
    var originalBtnText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');
    
    $.ajax({
        url: '../api/feedback.php?action=submit',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(resp) {
            if(resp.success) {
                $('#fb-success').html("<div class='alert alert-success'>Feedback submitted successfully!</div>").show();
                setTimeout(function() { 
                    $('#feedback-modal').modal('hide');
                    $('.modal-backdrop').remove();
                    fetchAppointments();
                }, 1000);
            } else {
                $('#fb-success').html("<div class='alert alert-danger'>" + (resp.error || "An error occurred. Please try again.") + "</div>").show();
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to submit feedback. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.error || errorMsg;
            } catch (e) {}
            $('#fb-success').html("<div class='alert alert-danger'>" + errorMsg + "</div>").show();
            $submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});

// Reset form when modal is closed
$('#feedback-modal').on('hidden.bs.modal', function () {
    $('#feedback-form')[0].reset();
    $('#fb-success').hide().empty();
    $('#feedback-form button[type="submit"]').prop('disabled', false).html('Submit Feedback');
});
</script>
</body>
</html>