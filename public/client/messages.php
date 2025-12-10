<?php
require_once __DIR__.'/../../app/middleware/auth_middleware.php';
require_once __DIR__.'/../includes/navbar.php';
require_once __DIR__.'/../../app/middleware/role_middleware.php';
require_role('client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Messages | ConsultEASE</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
  <style>
    body { background:linear-gradient(120deg,#eaf2fa 0%,#d5e3fa 100%); }
    .chat-container{display:flex;height:78vh;max-height:650px;background:#fff;box-shadow:0 4px 36px #1976d21a;border-radius:22px;overflow:hidden;margin:auto;margin-top:2.4rem;max-width:960px;}
    .conv-list{width: 320px;background:#f9fbfd;border-right:1.5px solid #e6edf4;overflow-y:auto;}
    .conv-list .conv-user{cursor:pointer;padding:.89em 1.2em;border-bottom:1px solid #ececec;transition:.12s;background:none;display:flex;gap:9px;align-items:center;}
    .conv-user.active, .conv-user:hover{background:#e3f2fd;}
    .conv-user .avatar{width:40px;height:40px;border-radius:50%;background:#1675cc;color:#fff;font-size:1.25em;display:flex;align-items:center;justify-content:center;font-weight:bold;box-shadow:0 0 6px #1675cc25;}
    .conv-user .conv-name{font-weight:600;font-size:1.01em;color:#1870ba;}
    .conv-user .conv-snippet{font-size:.95em;color:#5b738a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .conv-user .conv-meta{margin-left:auto;text-align:right;}
    .conv-user .conv-time{font-size:.93em;color:#9cb3c5;letter-spacing:.01em;}
    .unread-dot{display:inline-block;width:10px;height:10px;background:#198754;border-radius:100%;position:relative;top:2px;margin-left:4px;}
    .messages-pane{flex:1;display:flex;flex-direction:column;overflow:hidden;background:#f5faff;}
    .messages-header{padding:.95em 1.4em .69em 1.5em;border-bottom:1.5px solid #dde9f5;background:#fdfdfe;font-weight:700;font-size:1.1em;color:#003A6C;letter-spacing:.01em;}
    .message-list{flex:1;overflow-y:auto;padding:1.6em 1.5em 1.5em 1.5em;display:flex;flex-direction:column;gap:.62em;}
    .bubble{display:inline-block;max-width:70%;padding:.65em 1.1em;border-radius:15px;font-size:1.07em;box-shadow:0 1px 6px #1976d210;position:relative;}
    .bubble.client{background:#1563a9;color:#fff;margin-left:auto;border-bottom-right-radius:8px 20px;}
    .bubble.consultant{background:#e3f2fd;color:#14456c;border-bottom-left-radius:8px 20px;}
    .bubble .msg-time{display:block;font-size:.91em;color:#ddeafb;margin-top:2px;float:right;margin-left:8px;}
    .bubble.consultant .msg-time{color:#7e98b6;}
    .msg-meta{font-size:.91em;color:#3770a5;display:inline-block;margin-right:10px;}
    .msg-compose{padding:1.1em 1.7em 1.1em 1.5em;border-top:1.5px solid #dde9f5;background:#fff;}
    .msg-compose-row{display:flex;align-items:center;gap:13px;}
    .msg-compose textarea{flex:1;border-radius:14px;resize:none;min-height:44px;max-height:78px;font-size:1.09em;background:#f4fafe;border:1.5px solid #bdd6e6;margin-right:3px;transition:.13s;}
    .msg-compose textarea:focus{border-color:#0070b8;box-shadow:0 0 0 2px #0070b833;}
    .msg-send-btn{background:#0567f9;color:#fff;border-radius:15px;padding:.52em 2.13em;font-weight:600;font-size:1.09em;transition:.13s;}
    .msg-send-btn:active,.msg-send-btn:focus{background:#1665bb;}
    @media(max-width:900px){.chat-container{flex-direction:column;height:100vh;min-height:600px;}.conv-list{width:100%;max-height:160px;border-right:none;border-bottom:1.5px solid #e6edf4;}.messages-pane{flex:1;}}
  </style>
</head>
<body>
<div class="container">
  <h2 class="text-center mt-4 mb-3" style="color:#003A6C;font-weight:900;">Messages</h2>
  <div class="chat-container"><div class="conv-list" id="conv-list">
    <div class="text-center p-4"><span class="text-secondary">Loading...</span></div>
  </div>
  <div class="messages-pane d-flex flex-column">
    <div class="messages-header" id="messages-header">Select a conversation</div>
    <div class="message-list flex-grow-1" id="message-list" style="overflow-y:auto;"></div>
    <div class="msg-compose border-top" id="msg-compose" style="display:none;">
      <form id="send-msg-form" class="msg-compose-row">
        <textarea class="form-control" placeholder="Type your message..." id="msg-input"></textarea>
        <button type="submit" class="msg-send-btn"><i class="bi bi-send"></i></button>
      </form>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let currentChatUserId = null;
// Loads consultant list & merges with all threads for inbox UI
function loadConsultantInbox() {
  $('#conv-list').html('<div class="text-center p-4"><span class="text-secondary">Loading...</span></div>');
  // Load all consultants this client has ever booked
  $.getJSON('../api/appointments.php?action=list', function(appts) {
    let seen = {}, consultants = [];
    if(appts && appts.length){
      appts.forEach(a => {
        if(!seen[a.consultant_id]) {
          seen[a.consultant_id] = 1;
          consultants.push({
            id: a.consultant_id,
            name: a.consultant_name,
            email: a.consultant_email,
            pic: a.consultant_pic||"https://ui-avatars.com/api/?name="+encodeURIComponent(a.consultant_name)+"&background=0070b8&color=fff"
          });
        }
      });
    }
    // Load actual messages for previews
    $.getJSON('../api/messages.php?action=inbox', function(resp){
      let msgMap = {};
      if(resp.success && Array.isArray(resp.conversations)){
        resp.conversations.forEach(c=>{
          let key = c.sender_id == <?=intval($_SESSION['user_id'])?> ? c.recipient_id : c.sender_id;
          msgMap[key] = c;
        });
      }
      if(!consultants.length){$('#conv-list').html('<div class="text-center text-muted p-4">No conversations.</div>');return;}
      let html = consultants.map(c=>{
        let msg = msgMap[c.id]||{};
        let snippet = msg.content ? (msg.content.length>32?msg.content.substr(0,32)+'...':msg.content) : '<span class="small text-muted">Start a conversation</span>';
        let time = msg.sent_at?msg.sent_at.substr(5,11):'';
        return `<div class='conv-user' data-id='${c.id}'>
          <span class='avatar'><img src='${c.pic}' style='width:37px;height:37px;border-radius:100%;object-fit:cover'></span>
          <div><div class='conv-name'>${c.name}</div><div class='conv-snippet'>${snippet}</div></div><div class='conv-meta'><span class='conv-time'>${time}</span></div></div>`;
      }).join('');
      $('#conv-list').html(html);
    });
  });
}
function loadThread(user_id, user_name) {
  $('#messages-header').text(user_name);
  $('#msg-compose').show();
  $('#send-msg-form')[0].reset();
  $('#message-list').html('<div class="text-center text-muted">Loading...</div>');
  $.getJSON('../api/messages.php?action=thread&user_id='+user_id, function(resp){
    if(!resp.success||!resp.messages.length){
      $('#message-list').html('<div class="text-center text-muted py-5">No messages yet.</div>');return;
    }
    $('#message-list').html(resp.messages.map(m=>{
      let isMe = (m.sender_id == <?=intval($_SESSION['user_id'])?>);
      let cls = isMe?'client':'consultant';
      let time = m.sent_at.substr(11,5);
      return `<div class='bubble ${cls}'><span class='msg-meta'>${isMe?'You':'Consultant'}</span> ${m.content}<span class='msg-time'>${time}</span></div>`;
    }).join(''));
    $('#message-list').scrollTop($('#message-list')[0].scrollHeight);
    currentChatUserId = user_id;
  });
}
$('#conv-list').on('click','.conv-user',function(){
  $('#conv-list .conv-user').removeClass('active');
  $(this).addClass('active');
  let user_id = $(this).data('id'),name = $(this).find('.conv-name').text();
  loadThread(user_id,name);
});
$('#send-msg-form').on('submit',function(e){
  e.preventDefault();
  let content = $('#msg-input').val().trim();
  if(!content||!currentChatUserId)return;
  $.post('../api/messages.php?action=send', {recipient_id: currentChatUserId, content: content}, function(resp){
    if(resp.success) {
      $('#msg-input').val('');
      loadThread(currentChatUserId,$('#messages-header').text());
      loadConsultantInbox();
    }
  },'json');
});
// Initial load
loadConsultantInbox();
</script>
</body>
</html>
