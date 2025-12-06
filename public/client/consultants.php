<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$consultants = [
  [ 'name' => 'Dr. Anya Sharma', 'pic' => 'https://randomuser.me/api/portraits/women/68.jpg', 'tags' => ['AI','Innovation'], 'bio' => '10+ years at leading tech firms, authored 3 patents.', 'exp'=>'PhD, AI & Innovation' ],
  [ 'name' => 'Kwame Yeboah', 'pic' => 'https://randomuser.me/api/portraits/men/74.jpg', 'tags' => ['Strategy','Retail'], 'bio' => 'Retail ops, business strategy, ex-Accenture.', 'exp'=>'MBA, Strategy' ],
  [ 'name' => 'Ama Boateng', 'pic' => 'https://randomuser.me/api/portraits/women/85.jpg', 'tags'=>['Marketing','Product Dev'],'bio'=>'Top growth campaigns, SaaS launches, mentor.','exp'=>'MSc Marketing'],
  [ 'name' => 'Jason Kraal', 'pic' => 'https://randomuser.me/api/portraits/men/21.jpg', 'tags'=>['Finance','Tech'],'bio'=>'FP&A, fintech startups, author of FinTech Weekly.','exp'=>'CPA, FinTech'],
  [ 'name' => 'Maya Hassan', 'pic' => 'https://randomuser.me/api/portraits/women/43.jpg', 'tags'=>['Leadership'],'bio'=>'Leadership coach, global corp L&D director.','exp'=>'ICF Certified'],
  [ 'name' => 'David Chen', 'pic' => 'https://randomuser.me/api/portraits/men/9.jpg', 'tags'=>['Product','Tech'],'bio'=>'Product manager @ scale-ups in Europe and Asia.','exp'=>'BSc, Product Mgt'],
];
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
<script>
const consultants = <?= json_encode($consultants) ?>;
let activeTag = 'all';
function renderConsultantCards() {
  const kw = document.getElementById('consultantSearch').value.toLowerCase();
  const list = document.getElementById('consultantList');
  list.innerHTML = '';
  let shown = 0;
  consultants.forEach((c,i)=>{
    let matches = (activeTag==='all'||c.tags.includes(activeTag));
    if(matches && kw) {
      matches = c.name.toLowerCase().includes(kw);}
    if(matches){
      let el = document.createElement('div');
      el.className = 'consultant-card';
      el.innerHTML = `<div class='d-flex align-items-center mb-2'><img src='${c.pic}' class='consultant-profile-img'><div><b>${c.name}</b><br><span class='text-muted small'>${c.exp}</span></div></div><div class='mb-1 small'>${c.bio}</div><div class='mb-2'>${c.tags.map(t=>`<span class='consultant-tag'>${t}</span>`).join('')}</div><a href='book.php?consultant_id=${i+1}' class='btn btn-primary btn-sm w-100 mt-1'>Book</a>`;
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
renderConsultantCards();
</script>
</body>
</html>