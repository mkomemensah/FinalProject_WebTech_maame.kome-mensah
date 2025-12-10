<?php
require_once __DIR__.'/../../app/middleware/auth_middleware.php';
require_once __DIR__.'/../../app/middleware/role_middleware.php';
require_role('consultant');
require_once __DIR__.'/../../app/config/database.php';
$consultant_id = $_SESSION['user_id'];
$stats = [ 'pending'=>0, 'upcoming'=>0, 'feedback'=>0, 'avail'=>0 ];
$now = date('Y-m-d H:i:s');
// Upcoming confirmed sessions
$stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name AS client_name, u.email FROM appointments a JOIN availability v ON a.availability_id = v.availability_id JOIN users u ON a.client_id = u.user_id WHERE a.consultant_id = ? AND a.status = 'confirmed' AND CONCAT(v.date, ' ', v.start_time) >= ? ORDER BY v.date, v.start_time");
$stmt->execute([$consultant_id, $now]); $upcoming = $stmt->fetchAll(); $stats['upcoming']=count($upcoming);
// Pending approvals
$stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name as client_name FROM appointments a JOIN availability v ON a.availability_id = v.availability_id JOIN users u ON a.client_id = u.user_id WHERE a.consultant_id=? AND a.status='pending' ORDER BY v.date,v.start_time");
$stmt->execute([$consultant_id]); $pending = $stmt->fetchAll(); $stats['pending']=count($pending);
// Availability slots
$stmt = $pdo->prepare("SELECT * FROM availability WHERE consultant_id=? AND (date>CURDATE() OR (date=CURDATE() AND end_time>=CURTIME())) ORDER BY date,start_time");
$stmt->execute([$consultant_id]); $slots=$stmt->fetchAll(); $stats['avail']=count($slots);
// Feedback needed
$stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name as client_name FROM appointments a JOIN availability v ON a.availability_id = v.availability_id JOIN users u ON a.client_id = u.user_id LEFT JOIN feedback f ON f.appointment_id=a.appointment_id WHERE a.consultant_id=? AND a.status='completed' AND f.feedback_id IS NULL");
$stmt->execute([$consultant_id]); $feedbacks=$stmt->fetchAll(); $stats['feedback']=count($feedbacks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Consultant Dashboard | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
<style>
body{background:linear-gradient(120deg,#eaf2fa 0%,#d5e3fa 100%);font-family:'Segoe UI',Arial,sans-serif;}
.navbar-custom{background:#073165;padding:.95rem 2vw;box-shadow:0 2px 18px #07316525;}
.navbar-custom .nav-link, .navbar-custom .navbar-brand{color:#fff!important;font-weight:600;font-size:1.12rem;letter-spacing:0.5px;}
.navbar-custom .nav-link.active,.navbar-custom .nav-link:focus{color:#90defb!important;border-bottom:2.5px solid #28b9ff;}
.navbar-profile{background:#fff;border-radius:19px;padding:.3rem .9rem;margin-left:1rem;}
.navbar-profile .dropdown-toggle,.navbar-profile .dropdown-item{color:#073165!important;font-weight:500;}
.glass-card{background:rgba(255,255,255,.98);backdrop-filter:blur(2px);border-radius:20px;padding:2.2rem 1.8rem;box-shadow:0 7px 38px #0731651a;}
.card-scroll{display:flex;overflow-x:auto;gap:1.2rem;}
.session-card{background:#fefeff;border-radius:14px;box-shadow:0 2px 14px #003a6c11;min-width:235px;padding:1.2rem;flex:0 0 auto;}
.session-card .avatar{width:47px;height:47px;border-radius:50%;object-fit:cover;}
.session-meta{font-weight:600;font-size:1.09rem;margin-bottom:3px;}
.session-t{font-size:.93rem;color:#226bb3;margin-bottom:2px;gap:3px;display:inline-block;font-weight:500;}
.status-tag{border-radius:8px;padding:2px 8px;font-size:.78rem;background:#d5ebfd;color:#0070B8;font-weight:700;letter-spacing:0.2px;margin-right:2px;}
.btn.blue-accent{background:#059CFF!important;color:#fff;font-weight:600;border-radius:12px;padding:.54rem 1.3rem;box-shadow:0 1px 10px #059cff2e;}
.btn.blue-accent:active,.btn.blue-accent:focus{outline:none;box-shadow:0 2px 12px #059cff56;}
.approvals-table{width:100%;background:#f8fcff;border-radius:14px;padding:1rem;}
.approvals-table td,.approvals-table th{padding:.52rem .6rem;vertical-align:middle;font-size:.95rem;}
.avail-card{background:#fafffd;border-radius:15px;padding:1rem;margin-bottom:1.4rem;box-shadow:0 1px 8px #0070b810;}
.feedback-card{background:#fcfdfe;border-radius:17px;padding:1.1rem;margin-top:.3rem;box-shadow:0 1.5px 11px #0070b814;}
.badge-info{background:#effafd;color:#1976d2;font-weight:600;font-size:.97rem;}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
 <div class="container-fluid">
   <a class="navbar-brand" href="#">ConsultEASE</a>
   <form class="me-auto ms-4" style="min-width:240px;max-width:320px;"><input type="search" class="form-control form-control-sm" placeholder="Search"></form>
   <ul class="navbar-nav flex-row gap-3 ms-auto align-items-center">
     <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
     <li class="nav-item"><a class="nav-link" href="availability.php">Manage Availability</a></li>
     <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
     <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
     <li class="nav-item navbar-profile dropdown">
       <a class="dropdown-toggle nav-link" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-fill"></i> <?=htmlspecialchars($_SESSION['name'])?></a>
       <ul class="dropdown-menu dropdown-menu-end">
         <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
         <li><hr class="dropdown-divider"></li>
         <li><a class="dropdown-item" href="../api/auth.php?action=logout">Logout</a></li>
       </ul>
     </li>
   </ul>
 </div>
</nav>
<div class="container mb-5">
  <div class="row g-4 mb-1">
    <div class="col-xl-7">
      <div class="glass-card mb-4">
        <div class="fs-4 fw-bold mb-2">Upcoming Sessions <span class="badge bg-primary text-white ms-2"><?= $stats['upcoming']?></span></div>
        <div class="card-scroll">
          <?php foreach($upcoming as $appt): ?>
          <div class="session-card">
            <img class="avatar mb-2" src="https://ui-avatars.com/api/?name=<?=urlencode($appt['client_name'])?>&background=0070B8&color=fff" alt="">
            <div class="session-meta"><?=htmlspecialchars($appt['client_name'])?></div>
            <div class="status-tag mb-1">Confirmed</div>
            <div class="session-t"><?=htmlspecialchars($appt['date'])?> <?=htmlspecialchars($appt['start_time'])?> - <?=htmlspecialchars($appt['end_time'])?></div>
            <a href="appointments.php#appt-<?=$appt['appointment_id']?>" class="btn blue-accent btn-sm w-100">View</a>
          </div>
          <?php endforeach; if(!$upcoming):?><div class="session-card">No sessions scheduled.</div><?php endif;?>
        </div>
      </div>
      <div class="glass-card">
        <div class="fs-4 fw-bold mb-1">Pending Approvals <span class="badge bg-warning text-dark ms-2"><?=$stats['pending']?></span></div>
        <div class="table-responsive approvals-table">
        <table class="table table-borderless align-middle mb-0">
          <thead class="small text-muted">
            <tr><th>Name</th><th>Time</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach($pending as $req): ?>
            <tr>
              <td><img class="avatar me-1" src="https://ui-avatars.com/api/?name=<?=urlencode($req['client_name'])?>&background=f0ad4e&color=222" alt=""> <?=htmlspecialchars($req['client_name'])?></td>
              <td><?=htmlspecialchars($req['date'])?> <span class="text-muted small ms-1"><?=htmlspecialchars($req['start_time'])?></span></td>
              <td>
                <button class="btn btn-success btn-sm accept-appt" data-id="<?=$req['appointment_id']?>">Accept</button>
                <button class="btn btn-danger btn-sm reject-appt" data-id="<?=$req['appointment_id']?>">Reject</button>
              </td>
            </tr>
          <?php endforeach;if(!$pending): ?><tr><td colspan=3 class="text-muted">No pending requests.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
    <div class="col-xl-5">
      <div class="glass-card mb-4">
        <div class="fs-5 fw-bold mb-3">Manage Availability <span class="badge bg-info text-dark ms-2"><?= $stats['avail']?></span></div>
        <div class="avail-card mb-3">
          <div class="row mb-2">
            <div class="col-6 small">Upcoming Slot:</div>
            <div class="col-6 text-muted small">Add, edit, remove slots</div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <?php foreach($slots as $slot):?>
              <span class="badge badge-info"><?=htmlspecialchars($slot['date'])?> <?=substr($slot['start_time'],0,5)?>-<?=substr($slot['end_time'],0,5)?></span>
            <?php endforeach;if(!$slots):?><span class="text-muted">No available slots.</span><?php endif; ?>
          </div>
          <a href="availability.php" class="btn btn-outline-primary mt-3 card-btn w-100">+ Add Time Slot</a>
        </div>
      </div>
      <div class="glass-card feedback-card mb-3">
        <div class="fs-5 fw-bold mb-3">Feedback to Submit <span class="badge bg-warning text-dark ms-2"><?=$stats['feedback']?></span></div>
        <?php foreach($feedbacks as $todo): ?>
        <div class="mb-3 bg-white text-dark p-2 rounded">
          <b><?=htmlspecialchars($todo['client_name'])?></b> <span class="badge bg-info">Session Ended</span><br>
          <span class="small text-muted">On <?=htmlspecialchars($todo['date'])?> <?=htmlspecialchars($todo['start_time'])?></span>
          <a href="feedback.php?appointment_id=<?=$todo['appointment_id']?>" class="btn btn-primary btn-sm ms-2 mt-2">Leave Feedback</a>
        </div>
        <?php endforeach;if(!$feedbacks): ?><div class="text-secondary small">No feedback to submit.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div id="toast-wrap" style="position:fixed;right:23px;bottom:32px;z-index:9999;min-width:220px;"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function refreshConsultantDashboard() {
  $.getJSON('../api/appointments.php?action=list', function(appts) {
    // Upcoming Confirmed Sessions
    let now = new Date();
    let upcoming = appts.filter(a => a.status === 'confirmed' && (new Date(a.date + 'T' + a.start_time)) >= now);
    $('.badge.bg-primary').text(upcoming.length);
    let uphtml = '';
    if(upcoming.length) {
      uphtml = upcoming.map(a => `
      <div class="session-card">
        <img class="avatar mb-2" src="https://ui-avatars.com/api/?name=${encodeURIComponent(a.client_name)}&background=0070B8&color=fff" alt="">
        <div class="session-meta">${a.client_name}</div>
        <div class="status-tag mb-1">Confirmed</div>
        <div class="session-t">${a.date} ${a.start_time} - ${a.end_time}</div>
        <a href="appointments.php#appt-${a.appointment_id}" class="btn blue-accent btn-sm w-100">View</a>
      </div>`)
      .join('');
    } else {
      uphtml = '<div class="session-card">No sessions scheduled.</div>';
    }
    $('.card-scroll').html(uphtml);

    // Pending Approvals
    let pending = appts.filter(a => a.status === 'pending');
    $('.badge.bg-warning').first().text(pending.length);
    let phtml = '';
    if (pending.length) {
      phtml = pending.map(req => `
        <tr>
          <td><img class="avatar me-1" src="https://ui-avatars.com/api/?name=${encodeURIComponent(req.client_name)}&background=f0ad4e&color=222"> ${req.client_name}</td>
          <td>${req.date} <span class="text-muted small ms-1">${req.start_time}</span></td>
          <td>
            <button class="btn btn-success btn-sm accept-appt" data-id="${req.appointment_id}">Accept</button>
            <button class="btn btn-danger btn-sm reject-appt" data-id="${req.appointment_id}">Reject</button>
          </td>
        </tr>
      `).join('');
    } else {
      phtml = '<tr><td colspan=3 class="text-muted">No pending requests.</td></tr>';
    }
    $('.approvals-table tbody').html(phtml);
  });
}
setInterval(refreshConsultantDashboard, 30000);
refreshConsultantDashboard();
// Accept/Reject AJAX
$(document).on('click', '.accept-appt, .reject-appt', function() {
  var btn = $(this);
  if (btn.prop('disabled')) return;
  btn.prop('disabled', true);
  var id = btn.data('id');
  var isAccept = btn.hasClass('accept-appt');
  var api = isAccept ? 'accept' : 'reject';
  $.post(`../api/appointments.php?action=${api}`, { appointment_id: id }, function(resp) {
    if (resp.success) {
      showToast(isAccept ? 'Appointment accepted!' : 'Appointment rejected.', isAccept);
    } else {
      showToast(resp.error||'Error: Could not update appointment.', false);
    }
    refreshConsultantDashboard();
  }, 'json').fail(function() {
    showToast('Server error: please try again or refresh.', false);
    refreshConsultantDashboard();
  });
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