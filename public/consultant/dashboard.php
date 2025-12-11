<?php
require_once __DIR__.'/../../app/middleware/auth_middleware.php';
require_once __DIR__.'/../../app/middleware/role_middleware.php';
require_role('consultant');
require_once __DIR__.'/../../app/config/database.php';
// Fix: map user_id to consultant_id
$stmt = $pdo->prepare("SELECT consultant_id FROM consultants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row = $stmt->fetch();
$consultant_id = $row ? $row['consultant_id'] : null;
$now = date('Y-m-d H:i:s');
if ($consultant_id) {
  // Next confirmed session
  $stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name AS client_name FROM appointments a JOIN availability v ON a.availability_id = v.availability_id JOIN users u ON a.client_id = u.user_id WHERE a.consultant_id = ? AND a.status = 'confirmed' AND CONCAT(v.date, ' ', v.start_time) >= ? ORDER BY v.date, v.start_time LIMIT 1");
  $stmt->execute([$consultant_id, $now]);
  $next = $stmt->fetch();
  // Pending approvals
  $stmt = $pdo->prepare("SELECT a.*, v.date, v.start_time, v.end_time, u.name as client_name FROM appointments a JOIN availability v ON a.availability_id = v.availability_id JOIN users u ON a.client_id = u.user_id WHERE a.consultant_id=? AND a.status='pending' ORDER BY v.date,v.start_time");
  $stmt->execute([$consultant_id]); $pending = $stmt->fetchAll();
  // Stats - Sessions/Feedback (optional)
  $week_start = date('Y-m-d', strtotime('monday this week'));
  // Accepted bookings all time ('confirmed' OR 'completed')
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE consultant_id=? AND status IN ('confirmed','completed')");
  $stmt->execute([$consultant_id]);
  $accepted_total = $stmt->fetchColumn();
  // Cancelled bookings all time ('cancelled')
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE consultant_id=? AND status = 'cancelled'");
  $stmt->execute([$consultant_id]);
  $cancelled_total = $stmt->fetchColumn();
} else {
  $next = false;
  $pending = [];
  $accepted_total = 0;
  $cancelled_total = 0;
}
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
.dashboard-stats{display:flex;flex-wrap:wrap;gap:1.4rem;margin-bottom:2.2rem;margin-top:.7rem;}
.stat-card{background:#fff;border-radius:18px;box-shadow:0 2px 12px #003a6c13;padding:1.5rem 2.2rem;min-width:170px;flex:1 1 170px;display:flex;flex-direction:column;align-items:center;}
.stat-card.blue{background:linear-gradient(98deg,#0070b8 7%,#e9f4fd 120%);color:#003A6C;box-shadow:0 3px 18px #0070b810;}
.stat-title{font-size:.97em;font-weight:600;color:#126aa8;}
.stat-value{font-size:2.2em;font-weight:800;line-height:1.1;color:#073165;}
.next-box{background:#fff;border-radius:16px;box-shadow:0 2px 14px #0070b810;margin-bottom:2.2rem;padding:1.7rem 2rem;}
.next-box.empty{background:#f6fbfe;color:#226bb3;text-align:center;}
.pending-card{background:rgba(255,255,255,.98);backdrop-filter:blur(2px);border-radius:19px;padding:2.2rem 1.8rem;margin-bottom:2.2rem;box-shadow:0 7px 38px #0731651a;}
.approvals-table{width:100%;margin-top:.8em;background: #f8fafc;border-radius:12px;padding:1rem 0;}
.approvals-table th,.approvals-table td{padding:.75rem .8rem;vertical-align:middle;font-size:1.09rem;}
.btn-blue-main{background:#0567f9!important;color:#fff;font-weight:700;padding:.62rem 2.5rem;font-size:1.12rem;border-radius:15px;}
.avail-mini{background:#e3f2fd;color:#146c43;text-align:center;border-radius:10px;padding:.67em 1em;margin-top:1.2rem;margin-bottom:1.7rem;font-size:1.09em;}
@media(max-width:900px){.dashboard-stats{flex-direction:column;gap:1.2rem;}}
.dropdown-menu, .dropdown-menu .dropdown-item { color: #003A6C !important; background: #fff !important; font-weight: 600; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
 <div class="container-fluid">
   <a class="navbar-brand" href="#">ConsultEASE</a>
   <ul class="navbar-nav flex-row gap-3 ms-auto align-items-center">
     <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
     <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
     <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
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
  <div class="dashboard-stats">
    <div class="stat-card"><div class="stat-title">Total Accepted Bookings</div><div class="stat-value"><?=$accepted_total?></div></div>
    <div class="stat-card blue"><div class="stat-title">Total Cancelled Bookings</div><div class="stat-value"><?=$cancelled_total?></div></div>
    <div class="stat-card"><div class="stat-title">See Full Appointments</div><a href="appointments.php" class="btn btn-blue-main mt-2">All Appointments</a></div>
  </div>
  <div class="next-box<?php if(!$next)echo' empty';?>">
  <?php if($next): ?>
    <div class="fs-5 mb-2 fw-bold text-secondary">Next Confirmed Session</div>
    <div class="d-flex align-items-center gap-3 mb-2"><div><b><?=htmlspecialchars($next['client_name'])?></b></div><span class="badge bg-primary">Upcoming</span></div>
    <div class="mb-1">Date: <b><?=htmlspecialchars($next['date'])?></b> | Time: <b><?=htmlspecialchars($next['start_time'])?> - <?=htmlspecialchars($next['end_time'])?></b></div>
    <a href="appointments.php#appt-<?=$next['appointment_id']?>" class="btn btn-outline-primary btn-sm">Session Details</a>
    <button class="btn btn-outline-success btn-sm mt-2 mark-as-completed" data-id="<?=$next['appointment_id']?>">Mark as Completed</button>
  <?php else: ?>
    <div class="text-secondary py-3">No confirmed sessions scheduled yet.</div>
  <?php endif; ?>
  </div>
  <div class="pending-card">
    <div class="fs-4 fw-bold mb-3">Pending Approvals</div>
    <div class="table-responsive approvals-table">
      <table class="table table-borderless align-middle mb-0">
        <thead class="small text-muted"><tr><th>Name</th><th>Time</th><th></th></tr></thead>
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
        <?php endforeach; if(!$pending): ?><tr><td colspan=3 class="text-muted">No pending requests.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div id="toast-wrap" style="position:fixed;right:23px;bottom:32px;z-index:9999;min-width:220px;"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// function refreshConsultantDashboard() {
//   $.getJSON('../api/appointments.php?action=list', function(appts) {
//     // Fix: map user_id to consultant_id
//     const user_id = <?=json_encode($_SESSION['user_id'])?>;
//     let consultant_id = null;
//     if (appts && appts.length) {
//       const user = appts.find(a => a.user_id === user_id);
//       if (user) {
//         consultant_id = user.consultant_id;
//       }
//     }

//     if (!consultant_id) {
//       $('.next-box').addClass('empty').html(`<div class="text-secondary py-3">No consultant profile found.</div>`);
//       $('.approvals-table tbody').html('<tr><td colspan=3 class="text-muted">No pending requests.</td></tr>');
//       $('.stat-card.blue').html('<div class="stat-title">Total Cancelled Bookings</div><div class="stat-value">0</div>');
//       $('.stat-card').eq(0).html('<div class="stat-title">Total Accepted Bookings</div><div class="stat-value">0</div>');
//       return;
//     }

//     const now = new Date();
//     // Next confirmed session
//     const confirmed = appts.filter(a => a.status === 'confirmed');
//     if (confirmed.length) {
//       const next = confirmed.sort((a,b)=>{
//         return new Date(a.date + 'T' + a.start_time) - new Date(b.date + 'T' + b.start_time);
//       })[0];
//       $('.next-box').removeClass('empty').html(`
//         <div class='fs-5 mb-2 fw-bold text-secondary'>Next Confirmed Session</div>
//         <div class='d-flex align-items-center gap-3 mb-2'><div><b>${next.client_name}</b></div><span class='badge bg-primary'>Upcoming</span></div>
//         <div class='mb-1'>Date: <b>${next.date}</b> | Time: <b>${next.start_time} - ${next.end_time}</b></div>
//         <a href="appointments.php#appt-${next.appointment_id}" class="btn btn-outline-primary btn-sm">Session Details</a>
//         <button class="btn btn-outline-success btn-sm mt-2 mark-as-completed" data-id="${next.appointment_id}">Mark as Completed</button>
//       `);
//     } else {
//       $('.next-box').addClass('empty').html(`<div class="text-secondary py-3">No confirmed sessions scheduled yet.</div>`);
//     }
//     // Pending approvals
//     let pending = appts.filter(a => a.status === 'pending');
//     let phtml = '';
//     if (pending.length) {
//       phtml = pending.map(req => `
//         <tr>
//           <td><img class="avatar me-1" src="https://ui-avatars.com/api/?name=${encodeURIComponent(req.client_name)}&background=f0ad4e&color=222"> ${req.client_name}</td>
//           <td>${req.date} <span class="text-muted small ms-1">${req.start_time}</span></td>
//           <td>
//             <button class="btn btn-success btn-sm accept-appt" data-id="${req.appointment_id}">Accept</button>
//             <button class="btn btn-danger btn-sm reject-appt" data-id="${req.appointment_id}">Reject</button>
//           </td>
//         </tr>
//       `).join('');
//     } else {
//       phtml = '<tr><td colspan=3 class="text-muted">No pending requests.</td></tr>';
//     }
//     $('.approvals-table tbody').html(phtml);

//     // Stats - Sessions/Feedback (optional)
//     // Accepted bookings all time ('confirmed' OR 'completed')
//     const accepted_stmt = $pdo.prepare("SELECT COUNT(*) FROM appointments WHERE consultant_id=? AND status IN ('confirmed','completed')");
//     accepted_stmt.execute([consultant_id]);
//     const accepted_total = accepted_stmt.fetchColumn();
//     $('.stat-card').eq(0).html('<div class="stat-title">Total Accepted Bookings</div><div class="stat-value">'+accepted_total+'</div>');

//     // Cancelled bookings all time ('cancelled')
//     const cancelled_stmt = $pdo.prepare("SELECT COUNT(*) FROM appointments WHERE consultant_id=? AND status = 'cancelled'");
//     cancelled_stmt.execute([consultant_id]);
//     const cancelled_total = cancelled_stmt.fetchColumn();
//     $('.stat-card.blue').html('<div class="stat-title">Total Cancelled Bookings</div><div class="stat-value">'+cancelled_total+'</div>');
//   });
// }
// setInterval(refreshConsultantDashboard, 30000);
// refreshConsultantDashboard();
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
    // refreshConsultantDashboard(); // This line is removed as per the edit hint
  }, 'json').fail(function() {
    showToast('Server error: please try again or refresh.', false);
    // refreshConsultantDashboard(); // This line is removed as per the edit hint
  });
});
$(document).on('click', '.mark-as-completed', function() {
  var btn = $(this);
  if(btn.prop('disabled')) return;
  btn.prop('disabled', true);
  var id = btn.data('id');
  $.post('../api/appointments.php?action=mark_completed', { appointment_id: id }, function(resp) {
    if (resp.success) {
      showToast('Session marked as completed!', true);
    } else {
      showToast(resp.error||'Error: Could not mark completed.', false);
    }
    // refreshConsultantDashboard(); // This line is removed as per the edit hint
  }, 'json').fail(function() {
    showToast('Server error: please try again or refresh.', false);
    // refreshConsultantDashboard(); // This line is removed as per the edit hint
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