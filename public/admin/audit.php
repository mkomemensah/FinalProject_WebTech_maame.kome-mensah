<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('admin');
require_once __DIR__ . '/../../app/utils/audit.php';

$entries = fetch_audit(500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin | Audit Log</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>.mono {font-family: monospace; font-size:0.92rem;}</style>
</head>
<body>
<div class="container mt-4">
  <div class="card admin-card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div>
        <h3 class="mb-0" style="color:#003A6C;">Admin Audit Log</h3>
        <div class="small text-muted">Recent administrative actions (who, action, target, details, IP, time).</div>
      </div>
      <div class="text-end">
        <a href="dashboard.php" class="btn btn-outline-secondary me-2">Back</a>
        <a href="javascript:location.reload()" class="btn btn-primary">Refresh</a>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-sm-6">
          <input id="audit-search" class="form-control" placeholder="Search audit (admin, action, target or details)" />
        </div>
        <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
          <small class="text-muted">Showing latest <?= count($entries) ?> entries</small>
        </div>
      </div>

      <?php if(!$entries): ?>
        <div class="alert alert-info">No audit entries yet.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table id="audit-table" class="table table-hover align-middle">
            <thead class="table-light"><tr><th>When</th><th>Admin</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
              <?php foreach($entries as $e):
                $details = $e['details'] ?? '';
                $detailsEscAttr = htmlspecialchars($details, ENT_QUOTES);
                $short = mb_strlen($details) > 120 ? htmlspecialchars(mb_substr($details,0,120), ENT_QUOTES) . '…' : htmlspecialchars($details, ENT_QUOTES);
              ?>
              <tr>
                <td class="mono" style="white-space:nowrap"><?= htmlspecialchars($e['created_at']) ?></td>
                <td>
                  <div class="fw-bold"><?= htmlspecialchars($e['admin_name']) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($e['admin_email']) ?></div>
                </td>
                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($e['action']) ?></span></td>
                <td><?= htmlspecialchars($e['target_type']) ?> <?= $e['target_id'] ? '#'.htmlspecialchars($e['target_id']) : '' ?></td>
                <td>
                  <div class="details-preview mono" data-full="<?= $detailsEscAttr ?>"><?= $short ?> <?php if(mb_strlen($details) > 120): ?><a href="#" class="toggle-details">Show</a><?php endif; ?></div>
                </td>
                <td class="text-monospace small"><?= htmlspecialchars($e['ip_address']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Client-side search
document.addEventListener('DOMContentLoaded', function(){
  const search = document.getElementById('audit-search');
  const table = document.getElementById('audit-table');
  if(search && table){
    search.addEventListener('input', function(){
      const q = this.value.toLowerCase();
      Array.from(table.tBodies[0].rows).forEach(row => {
        const txt = row.textContent.toLowerCase();
        row.style.display = txt.indexOf(q) === -1 ? 'none' : '';
      });
    });
  }

  // Toggle details expand/collapse
  document.querySelectorAll('.toggle-details').forEach(a => {
    a.addEventListener('click', function(ev){
      ev.preventDefault();
      const container = this.closest('.details-preview');
      if(!container) return;
      const full = container.getAttribute('data-full');
      if(this.textContent === 'Show'){
        container.innerHTML = '<span class="mono">' + escapeHtml(full) + '</span> <a href="#" class="toggle-details">Hide</a>';
      } else {
        const short = escapeHtml(full.substring(0,120)) + (full.length>120? '…':'');
        container.innerHTML = '<span class="mono">' + short + '</span> ' + (full.length>120? '<a href="#" class="toggle-details">Show</a>':'');
      }
    });
  });
});

function escapeHtml(str){
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
