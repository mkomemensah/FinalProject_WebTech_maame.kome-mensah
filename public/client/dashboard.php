<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$clientName = $_SESSION['name'] ?? 'Client';
$profilePic = 'https://ui-avatars.com/api/?name=' . urlencode($clientName) . '&background=003A6C&color=fff&size=64';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard | ConsultEASE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
  <style>
    body {
      background: radial-gradient(ellipse 90% 60% at 60% 0,#cce2f7 40%,#e6f0fc 100%); min-height:100vh;
    }
    .nav-custom {
      background: #012a4a;
      color:#fff !important;
      min-height:58px;
    }
    .nav-custom a, .nav-custom .nav-link, .nav-custom .navbar-brand {color:#fff!important; font-weight:600}
    .profile-mini { width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #63aaf9; }
    .dropdown-menu {background:#fff;color:#212529;}
    .dropdown-menu .dropdown-item {color:#212529!important;font-weight:500;}
    .filter-pill {border-radius:24px; background:#fff; color:#003A6C; padding:4px 18px; margin-right:7px; border:none; font-weight:500; box-shadow:0 1px 3px #0000000a}
    .filter-pill.active {background:#0070B8; color:#fff;}
    .consultant-card { background:#fff; border-radius:17px; box-shadow:0 2px 16px #003A6c10; padding:16px 14px; min-width:230px; margin-right:18px; transition:.14s transform; height:180px; display:flex; flex-direction:column; justify-content:space-between; }
    .consultant-card:hover { transform:translateY(-5px) scale(1.02); box-shadow:0 4px 32px #0070B840; }
    .consultant-list-scroll {overflow-x:auto; display:flex; padding-bottom:9px; margin-bottom:15px;}
    .consultant-profile {border-radius:50%;height:50px;width:50px;object-fit:cover;margin-right:10px;}
    .consultant-tag {background:#f2f6fb;color:#0070B8;font-size:.82rem;border-radius:3px;padding:2px 8px;margin-right:4px;}
    .consultant-card.active {border:2px solid #0070B8;}
    .detail-card {background:#012a4a;color:#fff;border-radius:17px!important; min-height:340px;}
    .detail-slot {background:#fff;color:#003A6C;border-radius:6px;padding:6px 12px;margin:4px 4px 4px 0;display:inline-block;font-weight:600;cursor:pointer;}
    .detail-slot.booked{background:#eaeaea;color:#aaa;cursor:not-allowed;}
    .main-panel {background:rgba(255,255,255,.85); border-radius:25px; padding:2rem 2rem 1.3rem 2rem; box-shadow:0 6px 32px #003A6C20; margin-bottom:2rem;}
    .account-panel {background:#ffffffdd;border-radius:15px;padding:1rem 2rem;margin-top:1.3rem;box-shadow:0 1px 8px #003A6c18;}
    .form-control, .form-select {border-radius:10px;}
    @media (max-width:991px) {
      .dashboard-split {flex-direction:column;}
      .main-panel {padding:1rem;}
      .account-panel{padding:1rem}
    }
  </style>
</head>
<body>
<!-- Custom nav -->
<nav class="navbar nav-custom mb-3">
  <div class="container-fluid px-4">
    <a class="navbar-brand me-5" style="font-size:1.6rem;">ConsultEASE</a>
    <ul class="navbar-nav flex-row gap-4 ms-auto align-items-center" style="gap:2.2rem!important">
  <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="consultants.php">Find a Consultant</a></li>
    <li class="nav-item"><a class="nav-link" href="appointments.php">My Appointments</a></li>
    <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
    <li class="nav-item"><a class="nav-link" href="faq.php">Support</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
          <img src="<?= $profilePic ?>" class="profile-mini me-2"> <span class="fw-bold me-1 clientNameDisplay"><?= htmlspecialchars($clientName) ?></span> <span class="ms-1"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
          
          <li><a class="dropdown-item" href="booking_history.php">Booking History</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="../api/auth.php?action=logout">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
</nav>
<!-- Professional dashboard arrangement -->
<div class="container py-4">
  <!-- Greeting -->
  <h2 class="mb-2" style="color:#003A6C;font-weight:800;">Welcome, <span class="clientNameDisplay"><?= htmlspecialchars($clientName) ?></span>!</h2>
  <!-- Filter/Search Row -->
  <div class="row mb-4">
    <div class="col-12 d-flex flex-wrap gap-3 align-items-center p-2" style="background:#f8fafc;border-radius:16px;box-shadow:0 1px 8px #003a6c08;">
      <button class="filter-pill active" onclick="filterConsultants('all')">All</button>
      <button class="filter-pill" onclick="filterConsultants('Marketing')">Marketing</button>
      <button class="filter-pill" onclick="filterConsultants('Product Dev')">Product Dev</button>
      <button class="filter-pill" onclick="filterConsultants('Leadership')">Leadership</button>
      <input class="form-control ms-auto" id="consultantSearch" style="max-width:250px;min-width:170px;" type="search" placeholder="Search..." oninput="searchConsultants()">
    </div>
  </div>

  <!-- Consultant Card Grid -->
  <div id="consultantList" class="row g-4 mb-5">
    <!-- JS will render cards as .col-md-6.col-lg-4 for grid effect -->
  </div>
  <!-- Consultant Detail (Optional: render as modal for better UX, keep card for now) -->
  <div id="consultantDetailCard" class="d-none"></div>

  <!-- Appointments CTA Card -->
  <div class="row justify-content-center mt-1">
    <div class="col-md-7 col-lg-5">
      <div class="card text-center shadow" style="border-radius:18px;">
        <div class="card-body py-4">
          <h3 class="mb-2" style="color:#003A6C;font-weight:700;">My Appointments</h3>
          <p class="mb-3" style="font-size:1.1rem;">View and manage all your bookings here.</p>
          <a href="appointments.php" class="btn btn-primary btn-lg w-100" style="font-size:1.15rem;padding:.85em 0;border-radius:20px;">Go to My Appointments</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modern profile update confirmation overlay -->
<div id="profile-toast" style="position:fixed;top:30px;left:50%;transform:translateX(-50%);z-index:3000;min-width:220px;max-width:370px;display:none;"></div>

<script>
let consultants = [];
let activeFilter = 'all', selectedIdx = 0;
function filterConsultants(tag) {
  activeFilter = tag;
  document.querySelectorAll('.filter-pill').forEach(b =>
    b.classList.toggle('active', b.textContent === tag || (tag==='all' && b.textContent==='All')));
  renderConsultants();
}
function searchConsultants() { renderConsultants(); }
// Render consultant cards into the grid
function renderConsultants() {
  const list = document.getElementById('consultantList');
  const keyword = document.getElementById('consultantSearch').value.toLowerCase();
  list.innerHTML = '';
  let shown = 0;
  consultants.forEach((c,i) => {
    let show = activeFilter==='all'||(c.expertise && c.expertise.toLowerCase().includes(activeFilter.toLowerCase()));
    if(show && keyword) {
      show = c.name.toLowerCase().includes(keyword) || (c.expertise && c.expertise.toLowerCase().includes(keyword));
    }
    if(show) {
      let el = document.createElement('div');
      el.className = 'col-md-6 col-lg-4';
      el.innerHTML = `<div class='consultant-card shadow-sm' style='background:#fff;border-radius:15px;box-shadow:0 1px 8px #003a6c10;padding:20px 15px;min-height:185px;height:100%;display:flex;flex-direction:column;justify-content:space-between;transition:0.12s;'><div class='d-flex align-items-center mb-3'><img src='${c.pic}' alt='${c.name}' style='width:54px;height:54px;border-radius:50%;object-fit:cover;margin-right:14px;border:2px solid #63aaf9;'><div><b class='h5 mb-0' style='color:#003A6C;'>${c.name}</b><br><span class='text-secondary small'>${c.expertise||''}</span></div></div><div class='small mb-2 text-muted'>${c.bio||''}</div><a href='book.php?consultant_id=${c.consultant_id}' class='btn btn-outline-primary btn-sm mt-auto px-3' style='border-radius:14px;'>Book Session</a></div>`;
      list.appendChild(el); shown++;
    }
  });
  if(!shown) {
    list.innerHTML = '<div class="text-muted text-center py-5">No consultants found.</div>';
  }
}
function showDetails() {
  if (!consultants.length) {
    document.getElementById('consultantDetailCard').innerHTML = '<div class="text-muted text-center py-5">Select a consultant to view details.<br>(No consultants found)</div>';
    return;
  }
  const c = consultants[selectedIdx];
  document.getElementById('consultantDetailCard').innerHTML=
    `<div class='p-4 text-center'><img src='${c.pic}' style='width:86px;height:86px;border-radius:50%;border:3px solid #63aaf9;margin-bottom:7px;'><div><h4 class='mb-0'>${c.name}</h4><div class='text-info small mb-2'>${c.expertise||''}</div><div class='mb-1'>${c.bio||''}</div></div><div class='mb-2'></div><a href='book.php?consultant_id=${c.consultant_id}' class='btn btn-primary btn-lg w-100 mt-3'>Book Session</a></div>`;
}
// On load, fetch consultant list from API
fetch('../api/consultants.php?action=list')
  .then(r=>r.json())
  .then(data=>{
    consultants = data;
    selectedIdx=0;
    renderConsultants();
    showDetails();
  });
$(function(){
  // Handle profile update via AJAX
  $('#profile-form').on('submit', function(e){
    e.preventDefault();
    var formData = $(this).serializeArray();
    var name = $('#client-name-input').val();
    $.post('../api/auth.php?action=update_profile', formData, function(resp){
      if(resp.success){
        if(resp.name) name = resp.name;
        $('.clientNameDisplay').text(name);
        var avatarSrc = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=003A6C&color=fff&size=64';
        $('.profile-mini').attr('src', avatarSrc);
        // Modern animated toast
        $('#profile-toast').stop(true,true).hide().html('<div class="alert alert-success shadow fw-bold mb-0" style="font-size:1.11em; border-radius:14px;">Profile updated successfully!</div>').fadeIn(180,function(){
            setTimeout(function(){ $('#profile-toast').fadeOut(400); }, 1600);
        });
      }else{
        $('#profile-toast').stop(true,true).hide().html('<div class="alert alert-danger shadow fw-bold mb-0" style="font-size:1.09em; border-radius:14px;">'+(resp.error||'Update failed')+'</div>').fadeIn(180,function(){
            setTimeout(function(){ $('#profile-toast').fadeOut(400); }, 2100);
        });
      }
    },'json');
  });

  // Booking history -- fetch real completed appointments with feedback
  function loadBookingHistory(){
    $.getJSON('../api/appointments.php?action=list', function(appts){
      var list = '';
      if(Array.isArray(appts)){
        var completed = appts.filter(a=>a.status==='completed' && (a.client_notes||a.consultant_notes));
        completed = completed.slice(0,4); // Show last 4 entries
        if(completed.length){
          completed.forEach(function(a){
            list += `<li>- ${a.consultant_name}, ${a.date}<br><span class='small text-secondary'>You: ${a.client_notes||''}</span><br><span class='small text-success'>Consultant: ${a.consultant_notes||''}</span></li>`;
          });
        }else{
          list = '<li>No feedback received yet.</li>';
        }
      }
      $('#booking-history-list').html(list);
    });
  }
  loadBookingHistory();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>