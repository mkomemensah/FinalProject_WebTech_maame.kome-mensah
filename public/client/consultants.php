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

<!-- View Details Modal -->
<div class="modal fade" id="consultantDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Consultant Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="consultantDetailsBody">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Leave Feedback</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="feedbackForm">
          <input type="hidden" id="consultantId" name="consultant_id">
          <div class="mb-3">
            <label for="rating" class="form-label">Rating</label>
            <select class="form-select" id="rating" name="rating" required>
              <option value="">Select a rating</option>
              <option value="5">5 - Excellent</option>
              <option value="4">4 - Very Good</option>
              <option value="3">3 - Good</option>
              <option value="2">2 - Fair</option>
              <option value="1">1 - Poor</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="comments" class="form-label">Comments</label>
            <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Submit Feedback</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let consultants = [];
let activeTag = 'all';
let currentConsultantId = null;

function renderConsultantCards() {
  const kw = document.getElementById('consultantSearch').value.toLowerCase();
  const list = document.getElementById('consultantList');
  list.innerHTML = '';
  let shown = 0;
  
  consultants.forEach((c) => {
    let matches = true;
    if (matches && kw) {
      matches = c.name.toLowerCase().includes(kw);
    }
    
    if (matches) {
      const el = document.createElement('div');
      el.className = 'consultant-card';
      el.innerHTML = `
        <div class='d-flex align-items-center mb-2'>
          <img src='${c.pic || '../assets/images/default-avatar.png'}' class='consultant-profile-img'>
          <div>
            <b>${c.name}</b><br>
            <span class='text-muted small'>${c.expertise || 'Consultant'}</span>
          </div>
        </div>
        <div class='mb-2 small'>${c.bio || 'No bio available.'}</div>
        <div class='d-flex gap-2'>
          <button class='btn btn-outline-primary btn-sm flex-grow-1 view-details' data-consultant-id='${c.consultant_id}'>
            <i class='bi bi-info-circle'></i> Details
          </button>
          <button class='btn btn-outline-success btn-sm flex-grow-1 give-feedback' data-consultant-id='${c.consultant_id}'>
            <i class='bi bi-chat-dots'></i> Feedback
          </button>
        </div>
        <a href='book.php?consultant_id=${c.consultant_id}' class='btn btn-primary btn-sm w-100 mt-2'>
          <i class='bi bi-calendar-plus'></i> Book Appointment
        </a>
      `;
      list.appendChild(el);
      shown++;
    }
  });
  
  // Add event listeners to the new buttons
  document.querySelectorAll('.view-details').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const consultantId = e.currentTarget.dataset.consultantId;
      showConsultantDetails(consultantId);
    });
  });
  
  document.querySelectorAll('.give-feedback').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const consultantId = e.currentTarget.dataset.consultantId;
      showFeedbackForm(consultantId);
    });
  });
  
  if (!shown) {
    list.innerHTML = '<div class="text-muted w-100 text-center py-4">No consultants found matching your criteria.</div>';
  }
}

function showConsultantDetails(consultantId) {
  const consultant = consultants.find(c => c.consultant_id == consultantId);
  if (!consultant) return;
  
  const modalBody = document.getElementById('consultantDetailsBody');
  modalBody.innerHTML = `
    <div class='text-center mb-3'>
      <img src='${consultant.pic || '../assets/images/default-avatar.png'}' class='rounded-circle mb-2' width='100' height='100'>
      <h4>${consultant.name}</h4>
      <p class='text-muted'>${consultant.expertise || 'Business Consultant'}</p>
    </div>
    <div class='mb-3'>
      <h6>About</h6>
      <p>${consultant.bio || 'No bio available.'}</p>
    </div>
    <div class='mb-3'>
      <h6>Expertise</h6>
      <div class='d-flex flex-wrap gap-2'>
        ${(consultant.expertise || '').split(',').map(e => `<span class='badge bg-primary'>${e.trim()}</span>`).join('')}
      </div>
    </div>
    <div class='d-grid gap-2'>
      <a href='book.php?consultant_id=${consultant.consultant_id}' class='btn btn-primary'>
        <i class='bi bi-calendar-plus'></i> Book Appointment
      </a>
    </div>
  `;
  
  const modal = new bootstrap.Modal(document.getElementById('consultantDetailsModal'));
  modal.show();
}

function showFeedbackForm(consultantId) {
  currentConsultantId = consultantId;
  document.getElementById('consultantId').value = consultantId;
  document.getElementById('feedbackForm').reset();
  const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
  modal.show();
}

// Handle feedback form submission
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn.innerHTML;
  
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
  
  // Here you would typically send this data to your server
  fetch('../api/feedback.php?action=submit', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Thank you for your feedback!');
      const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
      modal.hide();
    } else {
      throw new Error(data.error || 'Failed to submit feedback');
    }
  })
  .catch(error => {
    alert('Error: ' + error.message);
  })
  .finally(() => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnText;
  });
});
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