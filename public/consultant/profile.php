<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('consultant');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultant Profile | ConsultEASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
    <style>
    body{
      background: linear-gradient(115deg, #eaf2fa 0%, #d5e3fa 100%);
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .profile-card-glass {
      background: rgba(255,255,255,.98);
      backdrop-filter: blur(5px);
      border-radius: 22px;
      box-shadow: 0 10px 32px #0070b81a;
      padding: 2.3rem 2.67rem 2.2rem 2.67rem;
      margin:3rem auto 0 auto;
      max-width: 480px;
      min-width:260px;
    }
    .profile-headline {
      font-size:2.1rem;
      font-weight:700;
      color: #003A6C;
      text-align:center;
      margin-bottom: .6em;
      margin-top:1.06em;
    }
    .avatar-lg {
      width:74px; height:74px; border-radius: 50%; background: #003A6C; color:#fff; font-size:2.25rem; display:flex; align-items:center; justify-content:center; font-weight: bold; margin:0 auto .95em auto; border:2.5px solid #0070b8; box-shadow:0 0 18px #63a8e244;
    }
    .profile-form-label {
      font-size:1.08em; font-weight:600; color:#145688; letter-spacing:.01em;
    }
    .profile-form textarea, .profile-form input {
      border-radius: 13px; font-size:1.12em; min-height:43px; background:#f2f7fb; border:1.5px solid #d7e2ec; margin-bottom:7px;
    }
    .profile-form textarea:focus, .profile-form input:focus { border-color: #0070b8; box-shadow:0 0 0 1.5px #0070b855; }
    .profile-btn-lg {
      width:100%; background:#1976d2; color:#fff; border-radius:22px; padding:.90em 0; font-size:1.13em; font-weight:700; letter-spacing:.01em; transition:.18s;
      box-shadow:0 4px 24px #1976d20a;
      margin-top:6px;
    }
    .profile-btn-lg:hover { background:#1664b5; box-shadow:0 7px 28px #1976d229; }
    #profile-toast {
      z-index:9; position: absolute; left:0; right:0; top:-35px; width:86%; margin:auto; text-align:center; display:none;
    }
    .profile-illustration {
      display:block; margin:0 auto 1.1em auto; width: 68px; opacity:.85;
    }
    @media (max-width:600px){
      .profile-card-glass{padding: 1.5rem .9rem 1.45rem .9rem;}
      #profile-toast{width:96%;}
      .profile-headline{font-size:1.53rem;}
    }
    </style>
</head>
<body>
<div class="container">
  <div class="profile-headline">My Consultant Profile</div>
  <div class="profile-card-glass position-relative">
    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Avatar" class="profile-illustration"/>
    <div class="avatar-lg" id="avatar-initials"></div>
    <form class="profile-form" id="consultant-profile-form" method="post" action="<?= BASE_URL ?>api/consultants.php?action=update_profile" novalidate>
      <div id="profile-toast"></div>
      <div class="mb-3">
        <label class="profile-form-label">Bio</label>
        <textarea class="form-control" name="bio" rows="5" maxlength="550"></textarea>
      </div>
      <div class="mb-3">
        <label class="profile-form-label">Years of Experience</label>
        <input type="number" name="years_of_experience" min="0" inputmode="numeric" pattern="[0-9]*" class="form-control" required oninput="this.value = this.value.replace(/[^\d]/g,'')" />
      </div>
      <button type="submit" class="btn profile-btn-lg">Update Profile</button>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const initials = <?= json_encode(substr($_SESSION['name'],0,1)); ?> || "C";
$('#avatar-initials').text(initials);
$('#consultant-profile-form input[name="years_of_experience"]').on('keypress', function(e) {
    if (e.which < 48 || e.which > 57) {
        e.preventDefault();
    }
});
$('#consultant-profile-form').on('submit', function(e){
    e.preventDefault();
    var $form = $(this);
    var $years = $form.find('input[name="years_of_experience"]');
    if ($years.val()!=='' && !/^\d+$/.test($years.val())) {
        $years.addClass('is-invalid');
        return;
    } else {
        $years.removeClass('is-invalid');
    }
    $.post($form.attr('action'), $form.serialize(), function(resp) {
        if(resp.success) {
            $('#profile-toast').html('<div class="alert alert-success">Profile updated successfully!</div>').fadeIn();
            setTimeout(function(){ $('#profile-toast').fadeOut(); }, 2200);
        } else {
            $('#profile-toast').html('<div class="alert alert-danger">'+(resp.error||'Update failed')+'</div>').fadeIn();
        }
    }, 'json');
});
</script>
</body>
</html>