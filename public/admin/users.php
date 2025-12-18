<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | Manage Users</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3" style="color:#003A6C;">Manage Users</h2>
  <div class="d-flex gap-2 mb-3">
    <label for="admin-search" class="visually-hidden">Search users</label>
    <input id="admin-search" class="form-control" placeholder="Search users by name or email...">
    <button id="search-btn" class="btn btn-primary">Search</button>
    <button id="refresh-btn" class="btn btn-outline-secondary">Refresh</button>
  </div>
  <div id="users-table-area">
    <div class="text-center p-4 text-secondary">Loading users...</div>
  </div>
</div>
<script>
// Diagnostic: show that the users script is loaded
console.debug('admin/users.php script executing');
const ADMIN_API_LIST = '<?= rtrim(BASE_URL, "/") ?>/api/admin.php?action=list_users';

function renderUsers(data){
  if(!data || !data.success){ $('#users-table-area').html('<div class="p-3 text-danger">Failed to load users</div>'); return; }
  const users = data.users || [];
  if(users.length===0){ $('#users-table-area').html('<div class="p-3 text-secondary">No users found</div>'); return; }
    const rows = users.map(u=>{
      return `<tr><td>${escapeHtml(u.name)}</td><td>${escapeHtml(u.email)}</td><td>${escapeHtml(u.role)}</td><td>${escapeHtml(u.status)}</td><td>${escapeHtml(u.created_at||'')}</td><td><button class="btn btn-sm btn-info view-user" data-id="${u.user_id}">View</button> <button class="btn btn-sm btn-warning change-status" data-id="${u.user_id}" data-status="suspended">Suspend</button> <button class="btn btn-sm btn-success change-status" data-id="${u.user_id}" data-status="active">Activate</button></td></tr>`;
    }).join('');
  const html = `<table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;
  $('#users-table-area').html(html);
}

function loadUsers(){
  const area = document.getElementById('users-table-area');
  area.innerHTML = '<div class="text-center p-3 text-secondary">Loading users...</div>';
  const q = (document.getElementById('admin-search') || {value:''}).value || '';
  const url = ADMIN_API_LIST + '&search=' + encodeURIComponent(q);
  console.debug('Fetching users from', url);
  // If jQuery is available, use it; otherwise use fetch()
  if(window.jQuery){
    $.getJSON(url).done(function(resp){ renderUsers(resp); }).fail(function(){ area.innerHTML = '<div class="p-3 text-danger">Error fetching users</div>'; });
    return;
  }
  // fallback: fetch
  fetch(url, { credentials: 'same-origin' }).then(r=>r.json()).then(json=>{ renderUsers(json); }).catch(e=>{ console.error('fetch error', e); area.innerHTML = '<div class="p-3 text-danger">Error fetching users</div>'; });
}

function escapeHtml(s){ const d=document.createElement('div'); d.textContent = s||''; return d.innerHTML; }

function postAction(action, payload){
  const opts = { method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams(payload)};
  return fetch('<?= rtrim(BASE_URL, "/") ?>/api/admin.php?action='+action, opts).then(r=>r.json());
}

document.addEventListener('DOMContentLoaded', function(){
  // quick runtime check
  if(!window.jQuery) console.warn('jQuery not detected — using fetch fallback');
  loadUsers();
  document.getElementById('search-btn').addEventListener('click', loadUsers);
  document.getElementById('refresh-btn').addEventListener('click', function(){ document.getElementById('admin-search').value=''; loadUsers(); });

  document.body.addEventListener('click', function(ev){
    const t = ev.target;
    if(t.matches && t.matches('.change-status')){
      const id = t.dataset.id, status = t.dataset.status;
      if(!confirm('Change status?')) return;
      postAction('update_user_status', {id: id, status: status}).then(r=>{ if(r && r.success) loadUsers(); else alert(r.error||'Failed'); }).catch(()=>alert('Server error'));
    }
      // delete/restore removed — use Suspend/Activate actions which call update_user_status
    if(t.matches && t.matches('.view-user')){
      const id = t.dataset.id;
      fetch('<?= rtrim(BASE_URL, "/") ?>/api/admin.php?action=get_user&id='+encodeURIComponent(id), {credentials:'same-origin'})
        .then(r=>r.json()).then(r=>{ if(!r || !r.success){ alert('User not found'); return; } const u = r.user; alert('User: '+u.name+'\nEmail: '+u.email+'\nRole: '+u.role+'\nStatus: '+u.status); }).catch(()=>alert('Server error'));
    }
  });
});
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>
</html>