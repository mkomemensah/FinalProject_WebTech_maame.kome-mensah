<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Consultants | ConsultEASE</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
  <style>
    .consultant-card-list {display:flex;gap:2rem;flex-wrap:wrap;}
    .consultant-card {background:#fff;border-radius:18px;box-shadow:0 2px 18px #0070B825;padding:1.3rem;min-width:240px;width:280px;margin-bottom:22px;}
    .consultant-profile-img{height:48px;width:48px;border-radius:50%;object-fit:cover;margin-right:10px;border:2px solid #0070B850}
    .consultant-tag {background:#f2f6fb;color:#0070B8;font-size:.85rem;border-radius:3px;padding:2px 9px;margin-right:3px;}
    .card-body{padding: 0;}
  </style>
</head>
<body>
<div class="container py-4">
  <h2 style="color:#003A6C;">Available Consultants</h2>
  <div class="d-flex gap-2 flex-wrap mb-3 align-items-center">
    <button class="btn btn-outline-primary btn-sm filter-btn active" data-tag="all">All</button>
    <button class="btn btn-outline-primary btn-sm filter-btn" data-tag="Marketing">Marketing</button>
    <button class="btn btn-outline-primary btn-sm filter-btn" data-tag="Product Dev">Product Dev</button>
    <button class="btn btn-outline-primary btn-sm filter-btn" data-tag="Leadership">Leadership</button>
    <input class="form-control ms-2" id="consultantSearch" style="max-width:180px;" type="search" placeholder="Search by name..." oninput="renderConsultantCards()">
  </div>
  <div id="consultantList" class="consultant-card-list"></div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let consultants = [];
let activeTag = 'all';
function renderConsultantCards() {
  const kw = document.getElementById('consultantSearch').value.toLowerCase();
  const list = document.getElementById('consultantList');
  list.innerHTML = '';
  let shown = 0;
  consultants.forEach((c)=>{
    let matches = true; // DISABLE FILTERING FOR DEBUGGING
    if(matches && kw) {
      matches = c.name.toLowerCase().includes(kw);
    }
    if(matches){
      let el = document.createElement('div');
      el.className = 'consultant-card';
      el.innerHTML = `<div class='d-flex align-items-center mb-2'><img src='${c.pic}' class='consultant-profile-img'><div><b>${c.name}</b><br><span class='text-muted small'>${c.expertise || ''}</span></div></div><div class='mb-1 small'>${c.bio||''}</div><a href='book.php?consultant_id=${c.consultant_id}' class='btn btn-primary btn-sm w-100 mt-1'>Book</a>`;
      list.appendChild(el); shown++;
    }
  });
  if(!shown){list.innerHTML = '<div class="text-muted">No consultants found.</div>'}
}
document.querySelectorAll('.filter-btn').forEach(btn=>{
  btn.onclick = ()=>{
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    activeTag = btn.dataset.tag;
    renderConsultantCards();
  }
});
// INITIAL LOAD
$.getJSON('../api/consultants.php?action=list', function(data){
  consultants = data;
  renderConsultantCards();
});
</script>
</body>
</html>