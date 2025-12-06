<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$clientName = $_SESSION['name'] ?? 'Client';
$profilePic = 'https://ui-avatars.com/api/?name=' . urlencode($clientName) . '&background=003A6C&color=fff&size=64';
// Static consultant dataset for demo
$consultants = [
  [
    'name' => 'Dr. Anya Sharma',
    'pic' => 'https://randomuser.me/api/portraits/women/68.jpg',
    'tags' => ['AI', 'Innovation'],
    'bio' => '10+ years at leading tech firms, authored 3 patents.','slots'=>[1,2,10,'11A','Fri 9pm'],
    'exp' => 'PhD, AI & Innovation'
  ],
  [
    'name' => 'Kwame Yeboah',
    'pic' => 'https://randomuser.me/api/portraits/men/74.jpg',
    'tags' => ['Strategy','Retail'],
    'bio' => 'Retail operations, business strategy, ex-Accenture.', 'slots'=>[3,5,7,12],'exp'=>'MBA, Strategy'
  ],
  [
    'name' => 'Ama Boateng',
    'pic' => 'https://randomuser.me/api/portraits/women/85.jpg',
    'tags' => ['Marketing','Product Dev'],
    'bio' => 'Top growth campaigns, SaaS launches, mentor.','slots'=>[4,6,8,10,12],'exp'=>'MSc Marketing'
  ],
  [
    'name' => 'Jason Kraal',
    'pic' => 'https://randomuser.me/api/portraits/men/21.jpg',
    'tags' => ['Finance','Tech'],
    'bio' => 'FP&A, fintech startups, author of FinTech Weekly.','slots'=>[2,3,8,11],'exp'=>'CPA, FinTech'
  ],
  [
    'name' => 'Maya Hassan',
    'pic' => 'https://randomuser.me/api/portraits/women/43.jpg',
    'tags' => ['Leadership'],
    'bio' => 'Leadership coach, global corp L&D director.','slots'=>[7,8,9],'exp'=>'ICF Certified'
  ],
  [
    'name' => 'David Chen',
    'pic' => 'https://randomuser.me/api/portraits/men/9.jpg',
    'tags' => ['Product','Tech'],
    'bio' => 'Product manager @ scale-ups in Europe and Asia.','slots'=>[1,3,6,11],'exp'=>'BSc, Product Mgt'
  ]
];
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
      <li class="nav-item"><a class="nav-link" href="faq.php">Support</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
          <img src="<?= $profilePic ?>" class="profile-mini me-2"> <span class="fw-bold me-1"><?= htmlspecialchars($clientName) ?></span> <span class="ms-1"></span>
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
<div class="container">
  <div class="main-panel">
    <div class="d-lg-flex dashboard-split gap-5">
      <!-- CONSULTANT LIST -->
      <div class="flex-fill" style="min-width:320px;">
        <div class="mb-2"><h4 class="mb-1">Find a Consultant</h4></div>
        <div class="mb-2 d-flex gap-2 align-items-center">
          <button class="filter-pill active" onclick="filterConsultants('all')">All</button>
          <button class="filter-pill" onclick="filterConsultants('Marketing')">Marketing</button>
          <button class="filter-pill" onclick="filterConsultants('Product Dev')">Product Dev</button>
          <button class="filter-pill" onclick="filterConsultants('Leadership')">Leadership</button>
          <input class="form-control ms-2" id="consultantSearch" style="max-width:170px;" type="search" placeholder="Search..." oninput="searchConsultants()">
        </div>
        <div class="consultant-list-scroll" id="consultantList">
          <!-- Consultant cards (JS populates or fallback PHP loop below) -->
        </div>
      </div>
      <!-- DETAIL PANEL -->
      <div class="flex-fill" style="max-width:410px;min-width:320px;">
        <div class="card detail-card shadow" id="consultantDetailCard">
          <!-- Details content injected by JS, fallback below -->
        </div>
      </div>
    </div>
  </div>
  <!-- ACCOUNT/FEEDBACK/PASSWORD/BOOKINGS BOT PANEL -->
  <div class="account-panel mt-3 row g-4">
    <div class="col-md-5">
      <h5>My Profile</h5>
      <form>
        <div class="mb-2"><input class="form-control" value="<?= htmlspecialchars($clientName) ?>" placeholder="Name"></div>
        <div class="mb-2"><input class="form-control" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" placeholder="Email" disabled></div>
        <div class="mb-2"><input class="form-control" value="+233 ..." placeholder="Phone"></div>
        <button class="btn btn-outline-primary btn-sm mb-2">Update Profile</button>
      </form>
      <hr>
      <a href="change_password.php" class="btn btn-outline-secondary btn-sm">Update Password</a>
    </div>
    <div class="col-md-7">
      <h5>Consultant Feedback</h5>
      <div class="card mb-2">
        <div class="card-body">
          <strong>★★★★★</strong> <br>
          <span>Client successfully identified core growth areas. Recommended resources for market expansion.</span><br>
          <a href="#" class="btn btn-link btn-sm">View All Feedback</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <strong>Booking History</strong>
          <ul class="list-unstyled mb-0 ps-1">
            <li>- Strategy session with Kwame Yeboah, Mar 14</li>
            <li>- Leadership coaching with Maya Hassan, Mar 6</li>
            <li>- Tech session with Jason Kraal, Feb 28</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
const consultants = <?= json_encode($consultants) ?>;
let activeFilter = 'all', selectedIdx = 0;
function filterConsultants(tag) {
  activeFilter = tag;
  document.querySelectorAll('.filter-pill').forEach(b =>
    b.classList.toggle('active', b.textContent === tag || (tag==='all' && b.textContent==='All')));
  renderConsultants();
}
function searchConsultants() {
  renderConsultants();
}
function renderConsultants() {
  const list = document.getElementById('consultantList');
  const keyword = document.getElementById('consultantSearch').value.toLowerCase();
  list.innerHTML = '';
  let shown = 0;
  consultants.forEach((c,i) => {
    let show = activeFilter==='all'||c.tags.some(t=>t.toLowerCase().includes(activeFilter.toLowerCase()));
    if(show && keyword) {
      show = c.name.toLowerCase().includes(keyword) || c.tags.some(t=>t.toLowerCase().includes(keyword));
    }
    if(show) {
      let el = document.createElement('div');
      el.className = 'consultant-card'+(i===selectedIdx?' active':'');
      el.onclick = ()=>{selectedIdx=i;renderConsultants();showDetails();};
      el.innerHTML = `<div class='d-flex align-items-center mb-1'><img src='${c.pic}' class='consultant-profile'><div><b>${c.name}</b><br><span class='text-muted small'>${c.tags.join(', ')}</span></div></div><div class='small mb-0'>${c.bio}</div>`;
      list.appendChild(el);shown++;
    }
  });
  if(!shown) {
    list.innerHTML = '<div class="text-muted">No consultants found.</div>';
  }
}
function showDetails() {
  const c = consultants[selectedIdx];
  document.getElementById('consultantDetailCard').innerHTML=
    `<div class='p-4 text-center'><img src='${c.pic}' style='width:86px;height:86px;border-radius:50%;border:3px solid #63aaf9;margin-bottom:7px;'><div><h4 class='mb-0'>${c.name}</h4><div class='text-info small mb-2'>${c.exp}</div><div class='mb-1'>${c.bio}</div></div><div class='mb-2'>`+
    c.tags.map(t=>`<span class='consultant-tag'>${t}</span>`).join('')+`</div>`+
    `<a href='book.php?consultant_id=${selectedIdx+1}' class='btn btn-primary btn-lg w-100 mt-3'>Book Session</a></div>`;
}
renderConsultants();showDetails();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>