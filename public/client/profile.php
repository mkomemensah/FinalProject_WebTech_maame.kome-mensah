<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3" style="color:#003A6C;">My Profile</h2>
    <form class="card p-4 shadow-sm" id="profile-form" autocomplete="off" novalidate>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" id="profile-name-input" type="text" class="form-control" required value="<?= htmlspecialchars($_SESSION['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="text" class="form-control" disabled value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" id="profile-phone-input" type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
    <div id="profile-toast" style="position:fixed;top:30px;left:50%;transform:translateX(-50%);z-index:3000;min-width:220px;max-width:370px;display:none;"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
  $('#profile-form').on('submit', function(e){
    e.preventDefault();
    var name = $('#profile-name-input').val();
    var phone = $('#profile-phone-input').val();
    $.post('../api/auth.php?action=update_profile', {name: name, phone: phone}, function(resp){
      if(resp.success){
        $('#profile-toast').stop(true,true).hide().html('<div class="alert alert-success shadow fw-bold mb-0" style="font-size:1.11em; border-radius:14px;">Profile updated successfully!</div>').fadeIn(180,function(){
            setTimeout(function(){ $('#profile-toast').fadeOut(400); }, 1600);
        });
        // Optionally update the input values to be safe
        if(resp.name) $('#profile-name-input').val(resp.name);
      }else{
        $('#profile-toast').stop(true,true).hide().html('<div class="alert alert-danger shadow fw-bold mb-0" style="font-size:1.09em; border-radius:14px;">'+(resp.error||'Update failed')+'</div>').fadeIn(180,function(){
            setTimeout(function(){ $('#profile-toast').fadeOut(400); }, 2100);
        });
      }
    },'json');
  });
});
</script>
</body>
</html>