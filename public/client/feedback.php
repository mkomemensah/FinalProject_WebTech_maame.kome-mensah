<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$feedbacks = [
  [ 'consultant' => 'Kwame Yeboah', 'img' => 'https://randomuser.me/api/portraits/men/74.jpg', 'date'=>'2024-03-14', 'rating'=>5, 'comment'=>'Very helpful, helped clarify my business goals.' ],
  [ 'consultant' => 'Ama Boateng', 'img' => 'https://randomuser.me/api/portraits/women/85.jpg', 'date'=>'2024-03-06', 'rating'=>4, 'comment'=>'Creative advice, practical steps, would recommend.' ],
  [ 'consultant' => 'Jason Kraal', 'img' => 'https://randomuser.me/api/portraits/men/21.jpg', 'date'=>'2024-02-28', 'rating'=>5, 'comment'=>'Excellent session. Very clear and professional.' ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Consultant Feedback | ConsultEASE</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>.avatar{width:44px;height:44px;border-radius:50%;object-fit:cover;margin-right:12px;}.stars{color:#ffd500;font-size:1.1rem;letter-spacing:1px;}</style>
</head>
<body>
<div class="container py-4">
  <h2 style="color:#003A6C;">Consultant Feedback</h2>
  <div class="row">
    <?php foreach($feedbacks as $fb): ?>
    <div class="col-md-7 mb-3">
      <div class="card shadow-sm">
        <div class="card-body d-flex align-items-center">
          <img src="<?= $fb['img'] ?>" class="avatar">
          <div>
            <div><b><?= htmlspecialchars($fb['consultant']) ?></b> <span class="text-muted small"><?= htmlspecialchars($fb['date']) ?></span></div>
            <span class="stars"><?php for($i=0;$i<$fb['rating'];$i++)echo '★'; ?></span>
            <div class="small mt-1 text-secondary">"<?= htmlspecialchars($fb['comment']) ?>"</div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <a href="dashboard.php" class="btn btn-outline-primary mt-4">Back to Dashboard</a>
</div>
</body>
</html>
