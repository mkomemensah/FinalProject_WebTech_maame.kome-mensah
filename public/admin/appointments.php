<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('admin');
require_once __DIR__ . '/../../app/controllers/AppointmentController.php';
$appointments = AppointmentController::getAllAppointments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | Appointments</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3" style="color:#003A6C;">All Appointments</h2>
  <?php if(!$appointments): ?>
    <div class="alert alert-info">No appointments found.</div>
  <?php else: ?>
  <div class="admin-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h5 class="mb-0">Appointments History</h5>
        <div class="small appt-meta">Total: <?= count($appointments) ?> appointments</div>
      </div>
      <div>
        <input type="search" id="appt-search" class="form-control form-control-sm" placeholder="Search by client, consultant, or status" style="min-width:260px;">
      </div>
    </div>
  <table class="table appointments-table table-borderless">
    <thead class="table-light">
      <tr>
        <th>Client</th>
        <th>Consultant</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
        <th>Feedback / Details</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($appointments as $a): ?>
      <tr>
        <td><strong><?= htmlspecialchars($a['client_name']) ?></strong><div class="small text-muted"><?= htmlspecialchars($a['client_email']) ?></div></td>
        <td><strong><?= htmlspecialchars($a['consultant_name']) ?></strong><div class="small text-muted"><?= htmlspecialchars($a['consultant_email']) ?></div></td>
        <td class="appt-meta"><?= htmlspecialchars($a['date']) ?></td>
        <td class="appt-meta"><?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?></td>
        <td>
          <?php $s = htmlspecialchars($a['status']); ?>
          <span class="status-badge <?php echo 'status-'.($s?:''); ?>"><?php echo ucfirst($s); ?></span>
        </td>
        <td>
          <?php if($a['status'] === 'completed'): ?>
            <?php if(!empty($a['client_notes'])): ?><div class="small"><strong>Client:</strong> <?= nl2br(htmlspecialchars($a['client_notes'])) ?></div><?php endif; ?>
            <?php if(!empty($a['consultant_notes'])): ?><div class="small mt-1"><strong>Consultant:</strong> <?= nl2br(htmlspecialchars($a['consultant_notes'])) ?></div><?php endif; ?>
            <?php if(!empty($a['problem_description'])): ?><div class="small mt-1 text-muted"><strong>Problem:</strong> <?= nl2br(htmlspecialchars($a['problem_description'])) ?></div><?php endif; ?>
          <?php else: ?>
            <button class="btn btn-sm btn-primary btn-small view-details" data-appointment='<?= json_encode($a, JSON_HEX_APOS|JSON_HEX_QUOT) ?>'>View Details</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>

  <!-- Details modal -->
  <div class="modal fade" id="apptDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Appointment Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body" id="appt-details-body"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('click', function(ev){
      const t = ev.target;
      if(t.matches && t.matches('.view-details')){
        const raw = t.getAttribute('data-appointment');
        try{ const appt = JSON.parse(raw);
          let html = '<dl class="row">';
          html += '<dt class="col-sm-3">Client</dt><dd class="col-sm-9">'+(appt.client_name||'')+' &lt;'+(appt.client_email||'')+'&gt;</dd>';
          html += '<dt class="col-sm-3">Consultant</dt><dd class="col-sm-9">'+(appt.consultant_name||'')+' &lt;'+(appt.consultant_email||'')+'&gt;</dd>';
          html += '<dt class="col-sm-3">Date / Time</dt><dd class="col-sm-9">'+(appt.date||'')+' '+(appt.start_time||'')+' - '+(appt.end_time||'')+'</dd>';
          html += '<dt class="col-sm-3">Status</dt><dd class="col-sm-9">'+(appt.status||'')+'</dd>';
          if(appt.client_notes) html += '<dt class="col-sm-3">Client Feedback</dt><dd class="col-sm-9">'+(appt.client_notes||'')+'</dd>';
          if(appt.consultant_notes) html += '<dt class="col-sm-3">Consultant Feedback</dt><dd class="col-sm-9">'+(appt.consultant_notes||'')+'</dd>';
          if(appt.problem_description) html += '<dt class="col-sm-3">Problem</dt><dd class="col-sm-9">'+(appt.problem_description||'')+'</dd>';
          html += '</dl>';
          document.getElementById('appt-details-body').innerHTML = html;
          var modal = new bootstrap.Modal(document.getElementById('apptDetailsModal'));
          modal.show();
        }catch(e){ console.error(e); alert('Failed to parse details'); }
      }
    });
  </script>
  <script>
    // Simple client-side search/filter
    (function(){
      const input = document.getElementById('appt-search');
      if(!input) return;
      input.addEventListener('input', function(){
        const q = (this.value||'').toLowerCase().trim();
        const rows = document.querySelectorAll('.appointments-table tbody tr');
        rows.forEach(r => {
          const txt = r.innerText.toLowerCase();
          r.style.display = q === '' || txt.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    })();
  </script>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>