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
// Demo: Load from session all session keys that match 'booked_*'
session_start();
$appointments = [];
foreach($_SESSION as $k=>$v){
  if(strpos($k,'booked_')===0 && $v===true){
    $parts = explode('_',$k);
    $consultname = $parts[1].' '.$parts[2];
    $slot = $parts[3];
    $date = $parts[4];
    // Find which consultant
    $idx = 0;
    foreach($consultants as $i=>$c)if(strpos($consultname, explode(' ',$c['name'])[0])!==false)$idx=$i;
    $appointments[] = ['consultant_id'=>$idx,'consultant'=>$consultants[$idx]['name'],'img'=>$consultants[$idx]['pic'],'date'=>$date,'slot'=>$slot,'status'=>'Pending','notes'=>'Request pending confirmation'];
  }
}
// Add demo past appts
date_default_timezone_set('Africa/Accra');
$appointments[] = ['consultant_id'=>1,'consultant'=>$consultants[1]['name'],'img'=>$consultants[1]['pic'],'date'=>date('Y-m-d',strtotime('-18 days')),'slot'=>'3','status'=>'Completed','notes'=>'Great feedback!'];
$appointments[] = ['consultant_id'=>2,'consultant'=>$consultants[2]['name'],'img'=>$consultants[2]['pic'],'date'=>date('Y-m-d',strtotime('-4 days')),'slot'=>'10','status'=>'Completed','notes'=>'Consultation successful.'];
// Sort
usort($appointments,function($a,$b){return strcmp($b['date'],$a['date']);});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>My Appointments | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
.appt-card{border-radius:16px;box-shadow:0 1px 10px #003a6c21;margin-bottom:20px;}
.badge-pending{background:#f7b924;color:#222}
.badge-completed{background:#4cc790;color:#fff}
.appt-img{width:50px;height:50px;border-radius:50%;object-fit:cover;}
</style>
</head>
<body>
<div class="container py-4">
  <h2 style="color:#003A6C;">My Appointments</h2>
  <div class="row">
  <?php
  $today = date('Y-m-d');
  $hasUpcoming=false;$hasPast=false;
  foreach($appointments as $a){
    $isUpcoming = $a['date']>=$today;
    $badge = $a['status']==='Completed'?'badge-completed':'badge-pending';
    if($isUpcoming&&!$hasUpcoming){echo '<h5 class="mt-4 mb-2">Upcoming</h5>';$hasUpcoming=true;}
    if(!$isUpcoming&&!$hasPast){echo '<h5 class="mt-4 mb-2">Past</h5>';$hasPast=true;}
    ?>
    <div class="col-md-7">
      <div class="card appt-card">
        <div class="card-body d-flex align-items-center">
          <img src="<?= $a['img'] ?>" class="appt-img me-3">
          <div>
            <div><b><?= htmlspecialchars($a['consultant']) ?></b> <span class="badge <?= $badge ?> ms-1"><?= $a['status'] ?></span></div>
            <div class="small text-muted">Date: <?= htmlspecialchars($a['date']) ?> | Slot: <?= htmlspecialchars($a['slot']) ?></div>
            <div class="small text-secondary"><?= htmlspecialchars($a['notes']) ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>
  <?php if(!$hasUpcoming&&!$hasPast): ?><div class="text-muted">No appointments found.</div><?php endif; ?>
  </div>
  <a href="dashboard.php" class="btn btn-outline-primary mt-4">Back to Dashboard</a>
</div>
</body>
</html>
