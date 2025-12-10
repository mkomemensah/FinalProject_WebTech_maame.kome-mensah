<?php
require_once __DIR__ . '/../../app/middleware/auth_middleware.php';
require_once __DIR__ . '/../../app/middleware/role_middleware.php';
require_role('client');
$faq_list = [
  [
    'q' => 'How do I find and book a consultant?',
    'a' => 'Use the "Find a Consultant" tab to browse or filter consultants by expertise. Click "Book" next to your chosen consultant to select a date/time and send a session request.'
  ],
  [
    'q' => 'How are appointments confirmed?',
    'a' => 'After you send a booking request, the consultant reviews and confirms or suggests a new time. You will receive a notification once confirmed.'
  ],
  [
    'q' => 'Can I reschedule or cancel a booking?',
    'a' => 'Yes! Go to "My Appointments" to see your bookings. There you can request to reschedule or cancel, subject to each consultant’s policy.'
  ],
  [
    'q' => 'How are consultants selected and vetted?',
    'a' => 'All consultants are carefully screened for qualifications and expertise before joining ConsultEASE. You can read about their background by clicking their name in the consultant finder.'
  ],
  [
    'q' => 'Is my privacy protected?',
    'a' => 'Yes. Your information and session details are private and accessible only to you and your booked consultants. Please see our privacy policy for more details.'
  ],
  [
    'q' => 'Still have questions?',
    'a' => 'Contact our support team anytime at <a href="mailto:consultease@gmail.com">consultease@gmail.com</a>.'
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Support & FAQ | ConsultEASE</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background: linear-gradient(120deg, #f6faff 60%, #e5f0ff 100%); min-height:100vh; }
    .support-main-panel {
      max-width: 650px;
      margin: 56px auto 0 auto;
      border-radius: 23px;
      background: linear-gradient(115deg, #fafdff 80%, #e6f2ff 100%);
      box-shadow: 0 8px 44px #003a6c18, 0 1.5px 3px #003a6c12;
      padding: 0 0 30px 0;
      position: relative;
    }
    .panel-header {padding: 32px 38px 15px 38px;}
    .support-title { color:#003A6C;font-size:2.08rem;font-weight:900;text-align:left;letter-spacing:.5px; margin-bottom:0.2em; }
    .chat-history { padding:28px 0 10px 0; max-height:325px; min-height:180px; overflow-y:auto; background: transparent; }
    .chat-shell { margin:0; border-radius:0; background:none; box-shadow:none; padding:0; }
    .chat-footer {border-top:1.5px solid #ecedf5;padding:14px 40px 12px 38px;background:none;display:flex;gap:10px;margin-bottom:0;}
    .chat-input {border-radius:16px;border:1.2px solid #b9cce1;flex:1;font-size:1rem;padding:11px 15px;background:#fff;}
    .chat-send-btn {border-radius:14px;background:linear-gradient(90deg,#0070B8 30%,#003A6C 98%);color:#fff;font-weight:600;padding:8px 23px;font-size:1.05em;border:0;}
    .chat-send-btn:active { filter:brightness(.93);}
    .chat-bubble-user {
      background: #fff;
      color: #162340;
      border-radius:22px 22px 4px 22px;
      padding: 16px 19px 13px 19px;
      min-width: 24%; max-width: 330px;
      margin-left:auto; margin-bottom: 12px;
      box-shadow: 0 2px 13px #003A6c06;
      font-size: 1.09em; position:relative; text-align: left; word-break:break-word;
    }
    .chat-bubble-bot {
      background: linear-gradient(98deg, #003A6C 17%, #0070B8 100%);
      color: #fff;
      border-radius:18px 22px 18px 6px;
      padding: 16px 19px 13px 24px;
      min-width: 28%; max-width: 340px;
      margin-right:auto; margin-bottom: 12px; margin-left:0;
      box-shadow: 0 2px 23px #003A6c16;
      font-size: 1.09em; position:relative; text-align: left; word-break:break-word;
      animation: fadein-chat 0.53s cubic-bezier(.36,1.25,.27,1);
    }
    @keyframes fadein-chat {
      from { opacity:0; transform:translateY(32px) scale(.97); }
      to { opacity:1; transform:none; }
    }
    .chat-bubble-user .avatar-user {
      width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #0070B8;position:absolute;right:-41px;top:2px;box-shadow: 0 2px 8px #3a70b870;
    }
    .chat-bubble-bot .avatar-bot {
      width:28px;height:28px;border-radius:50%;background:#fff;border:0;position:absolute;left:-39px;top:6px;box-shadow: 0 .5px 4px #0060b826; }
    .ai-label { display:inline-block; margin:9px 0 0 0; background:#fff; color:#585eed; font-weight:600; font-size:.94em; border-radius:6px; padding:2px 12px; letter-spacing:0.5px; box-shadow:0 1px 3px #002a6c10; }
    .divider-or { display:flex; align-items:center;justify-content:center;gap:14px; margin:42px 0 18px 0; }
    .divider-or-line { flex:1;height:1.6px;background:#ecedf5; border-radius:1.2px;}
    .divider-or-label {color:#749bcf;background:#ecedf5;font-weight:700;border-radius:11px;padding:6px 22px;font-size:1.05em;letter-spacing:.7px;}
    .faq-section {padding: 0 43px;}
    .accordion-item {margin-bottom:14px;border-radius:14px;overflow:hidden;border:none;}
    .accordion-button {font-weight:600;font-size:1.08em;color:#003a6c; border:none;background:#fafdff; transition:.1s; box-shadow:none;}
    .accordion-button:not(.collapsed){color:#0070b8;background:#ecf5ff;}
    .accordion-body {background:#fff;border-top:1px solid #e4eefd;padding:16px 18px 13px 18px;}
    .btn-back-ctn {text-align: center;margin-top:36px;}
    @media (max-width:700px){ .support-main-panel{border-radius:10px;}.panel-header{padding:18px 9vw 9px 6vw;}.chat-footer{padding:9px 8px 8px 8px;}.faq-section{padding:0 4vw;} }
  </style>
</head>
<body>
<div class="support-main-panel">
  <div class="panel-header">
    <div class="support-title mb-2">Support & Help Center</div>
    <div style="color:#2569a1;font-weight:400;font-size:1.13em;">Chat with our AI assistant, or browse our FAQs below for instant answers!</div>
  </div>
  <div class="chat-shell">
    <div id="chat-history" class="chat-history">
      <!-- Dynamic chat goes here -->
    </div>
    <div class="chat-footer">
      <input id="chat-input" class="chat-input" type="text" placeholder="Type your question…" autocomplete="off" />
      <button id="chat-send-btn" class="chat-send-btn">Send</button>
    </div>
  </div>
  <div class="divider-or mb-1"><div class="divider-or-line"></div><div class="divider-or-label">or browse FAQs</div><div class="divider-or-line"></div></div>
  <div class="faq-section">
    <div class="accordion" id="faqAccordion">
      <?php $idx=1; foreach($faq_list as $faq): ?>
      <div class="accordion-item">
        <h2 class="accordion-header" id="q<?=$idx?>"><button class="accordion-button <?php if($idx!==1)echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#c<?=$idx?>" aria-expanded="<?=($idx===1?'true':'false')?>" aria-controls="c<?=$idx?>"><?=$faq['q']?></button></h2>
        <div id="c<?=$idx?>" class="accordion-collapse collapse <?php if($idx===1)echo 'show'; ?>" aria-labelledby="q<?=$idx?>" data-bs-parent="#faqAccordion">
          <div class="accordion-body"><?=$faq['a']?></div>
        </div>
      </div>
      <?php $idx++; endforeach; ?>
    </div>
  </div>
</div>
<div class="btn-back-ctn">
  <a href="dashboard.php" class="btn btn-primary btn-lg fw-bold shadow-sm px-5 py-3 mt-2" style="font-size:1.18rem;">Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const FAQS = <?php echo json_encode($faq_list); ?>;
function scrollChatToBottom() {
  const chat = document.getElementById('chat-history');
  chat.scrollTop = chat.scrollHeight;
}
function addBubble(msg, role) {
  const $b = $('<div></div>').addClass(role === 'user' ? 'chat-bubble-user shadow position-relative' : 'chat-bubble-bot shadow position-relative');
  $b.html(msg);
  if(role === 'user') $b.append('<img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Me" class="avatar-user">');
  if(role === 'bot') $b.append('<img src="../assets/images/logo-icon.png" alt="AI Assistant" class="avatar-bot"><span class="ai-label">&#10024; Answered by AI</span>');
  $('#chat-history').append($b);
  scrollChatToBottom();
}
function aiMatchResponse(q) {
  const qLow = q.trim().toLowerCase();
  let best = null, bestScore = 0;
  FAQS.forEach(faq => {
      const sim = aiSimilarity(qLow, faq.q.toLowerCase());
      if(sim > bestScore) { best = faq.a; bestScore = sim; }
  });
  return bestScore > 0.35 ? best : null;
}
function aiSimilarity(a,b){
  // crude: word overlap + partial match
  const aWords = a.split(' '), bWords = b.split(' ');
  let hits = 0;
  aWords.forEach(w=>{if(b.includes(w)||bWords.some(bw=>bw.startsWith(w)||w.startsWith(bw)))hits++;});
  return (hits / bWords.length/1.4) + (a.length>10&&b.length>10?a.length/b.length/10:0);
}
function aiFallback() {
  return 'I couldn\'t find an exact answer, but our human support will reach out soon. Meanwhile, check FAQs below or email <a href="mailto:consultease@gmail.com">consultease@gmail.com</a>.';
}
function sendUserMsg(msg) {
  if(!msg.trim()) return;
  addBubble(msg, 'user');
  $('#chat-input').val('');
  setTimeout(function(){
    let resp = aiMatchResponse(msg);
    addBubble(resp ? resp : aiFallback(), 'bot');
  }, 900);
}
$('#chat-send-btn').on('click',function(){sendUserMsg($('#chat-input').val());});
$('#chat-input').on('keydown',function(e){if(e.key==='Enter'){e.preventDefault();sendUserMsg(this.value);}});
// Initial welcome message
addBubble('Hi! How can I help you today? You can ask anything about ConsultEASE.', 'bot');
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
</body>
</html>
