<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$consultants = [
  [ 'name' => 'Dr. Anya Sharma', 'pic' => 'https://randomuser.me/api/portraits/women/68.jpg' ],
  [ 'name' => 'Kwame Yeboah', 'pic' => 'https://randomuser.me/api/portraits/men/74.jpg' ],
  [ 'name' => 'Ama Boateng', 'pic' => 'https://randomuser.me/api/portraits/women/85.jpg' ],
  [ 'name' => 'Jason Kraal', 'pic' => 'https://randomuser.me/api/portraits/men/21.jpg' ],
  [ 'name' => 'Maya Hassan', 'pic' => 'https://randomuser.me/api/portraits/women/43.jpg' ],
  [ 'name' => 'David Chen', 'pic' => 'https://randomuser.me/api/portraits/men/9.jpg' ],
];
session_start();
$appointments = [];
foreach($_SESSION as $k=>$v){
  if(strpos($k,'booked_')===0 && $v===true){
    $parts = explode('_',$k);
    $consultid = intval($parts[1]);
    $date = $parts[2];
    $time = $parts[3];
    $notes = $_SESSION['notes_'.$consultid.'_'.$date.'_'.$time] ?? '';
    $appointments[] = [
      'consultant_id'=>$consultid,
      'consultant'=>$consultants[$consultid]['name'],
      'img'=>$consultants[$consultid]['pic'],
      'date'=>$date,
      'time'=>$time,
      'notes'=>$notes
    ];
  }
}
// Only future/booked appointments
$today = date('Y-m-d');
$future = array_filter($appointments,function($a)use($today){return $a['date']>=$today;});
usort($future,function($a,$b){return strcmp($a['date'],$b['date']);});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Booking History | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>.appt-card{border-radius:16px;box-shadow:0 1px 10px #003a6c21;margin-bottom:20px;} .appt-img{width:48px;height:48px;border-radius:50%;object-fit:cover;}</style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4" style="color:#003A6C;">My Future Bookings</h2>
  <?php if(!$future): ?><div class="alert alert-info">You have no upcoming bookings.</div><?php endif; ?>
  <div class="row">
  <?php foreach($future as $a): ?>
    <div class="col-md-7">
      <div class="card appt-card">
        <div class="card-body d-flex align-items-center">
          <img src="<?= $a['img'] ?>" class="appt-img me-3">
          <div>
            <div><b><?= htmlspecialchars($a['consultant']) ?></b></div>
            <div class="small text-muted">Date: <?= htmlspecialchars($a['date']) ?> | Time: <?= htmlspecialchars($a['time']) ?></div>
            <?php if($a['notes']): ?><div class="small text-secondary">Notes: <?= htmlspecialchars($a['notes']) ?></div><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <a href="dashboard.php" class="btn btn-primary mt-4">Back to Dashboard</a>
</div>
</body>
</html>
