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
  <h2 class="mb-3" style="color:#003A6C;">Admin Audit Log</h2>
  <p class="text-muted">Recent administrative actions (who, action, target, details, IP, time).</p>
  <?php if(!$entries): ?>
    <div class="alert alert-info">No audit entries yet.</div>
  <?php else: ?>
    <table class="table table-sm table-striped">
      <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr></thead>
      <tbody>
        <?php foreach($entries as $e): ?>
        <tr>
          <td class="mono"><?= htmlspecialchars($e['created_at']) ?></td>
          <td><?= htmlspecialchars($e['admin_name']) ?> <div class="small text-muted"><?= htmlspecialchars($e['admin_email']) ?></div></td>
          <td><?= htmlspecialchars($e['action']) ?></td>
          <td><?= htmlspecialchars($e['target_type']) ?> <?= $e['target_id'] ? '#'.htmlspecialchars($e['target_id']) : '' ?></td>
          <td><pre class="small" style="white-space:pre-wrap;"><?= htmlspecialchars($e['details']) ?></pre></td>
          <td><?= htmlspecialchars($e['ip_address']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <a href="dashboard.php" class="btn btn-outline-primary mt-3">Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
