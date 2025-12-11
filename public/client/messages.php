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
    .chat-container{display:flex;height:78vh;max-height:720px;background:#fff;box-shadow:0 4px 36px #1976d21a;border-radius:18px;overflow:hidden;margin:auto;margin-top:2.4rem;max-width:1000px;}
    .conv-list{width: 320px;background:#f9fbfd;border-right:1.5px solid #e6edf4;overflow-y:auto;}
    .conv-list .section-head{padding:.8rem 1rem;border-bottom:1px solid #eef6fb;background:#fbfdff;font-weight:700;color:#034a76}
    .conv-list .conv-user{cursor:pointer;padding:.75em 1rem;border-bottom:1px solid #f1f6fa;display:flex;gap:10px;align-items:center;}
    .conv-user.active, .conv-user:hover{background:#eaf6ff}
    .conv-user .avatar{width:44px;height:44px;border-radius:50%;background:#1675cc;color:#fff;font-size:1.1em;display:flex;align-items:center;justify-content:center;font-weight:700}
    .conv-user .conv-name{font-weight:700;color:#083a60}
    .conv-user .conv-snippet{font-size:.92em;color:#5b7282;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .messages-pane{flex:1;display:flex;flex-direction:column;overflow:hidden;background:#f7fbff}
    .messages-header{padding:1rem;border-bottom:1px solid #e6eef6;background:#fff;font-weight:700;color:#07324a}
    .message-list{flex:1;overflow-y:auto;padding:1.25rem;display:flex;flex-direction:column;gap:.6rem}
    .bubble{display:inline-block;max-width:72%;padding:.65em 1em;border-radius:12px;font-size:1rem}
    .bubble.me{background:#0070b8;color:#fff;margin-left:auto;border-bottom-right-radius:6px}
    .bubble.them{background:#e9f4fb;color:#0b3b5e;border-bottom-left-radius:6px}
    .bubble .meta{display:block;font-size:.82em;color:#86a7c4;margin-top:.25rem}
    .msg-compose{padding:1rem;border-top:1px solid #e6eef6;background:#fff}
    .msg-compose .form-control{border-radius:12px}
    @media(max-width:900px){.chat-container{flex-direction:column;height:100vh;}.conv-list{width:100%;max-height:180px;border-right:none;border-bottom:1px solid #e6edf4}}
  </style>
</head>
<body>
<div class="container">
  <h2 class="text-center mt-4 mb-3" style="color:#003A6C;font-weight:800;">Messages</h2>
  <div class="chat-container">
    <div class="conv-list">
      <div class="section-head">Conversations</div>
      <div id="conv-list" style="min-height:120px;"><div class="text-center p-4 text-secondary">Loading...</div></div>
      <div class="section-head">Start new chat</div>
      <div id="recipient-list" style="max-height:220px;overflow:auto;padding:6px 4px;"></div>
    </div>
    <div class="messages-pane d-flex flex-column">
      <div class="messages-header" id="messages-header">Select a conversation</div>
      <div class="message-list" id="message-list"></div>
      <div class="msg-compose" id="msg-compose" style="display:none;">
        <form id="send-msg-form" class="d-flex">
          <textarea id="msg-input" name="content" class="form-control me-2" placeholder="Type a message" rows="2"></textarea>
          <button id="send-btn" class="btn btn-primary" disabled><i class="bi bi-send"></i></button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let currentChatUserId = null;
let currentChatName = '';

function safeHtml(s){ return $('<div/>').text(s).html(); }

function showError(msg){ alert(msg); }

function loadUsers(){
  $('#recipient-list').html('<div class="text-center p-3 text-secondary">Loading...</div>');
  $.getJSON('../api/messages.php?action=users').done(function(resp){
    if(!resp || !resp.success){
      console.error('messages.php?action=users returned error', resp);
      $('#recipient-list').html('<div class="p-3 text-danger">Could not load users</div>');
      return;
    }
    const users = resp.users || [];
    if(users.length===0){ $('#recipient-list').html('<div class="p-3 text-secondary">No users available</div>'); return; }
    const html = users.map(u=>`<div class="conv-user" data-id="${u.id}" data-name="${safeHtml(u.name)}"><div class="avatar">${safeHtml(u.name.charAt(0)||'U')}</div><div><div class="conv-name">${safeHtml(u.name)}</div><div class="conv-snippet text-muted small">${safeHtml(u.email||'')}</div></div></div>`).join('');
    $('#recipient-list').html(html);
    $('#recipient-list .conv-user').on('click', function(){
      const id = $(this).data('id');
      const name = $(this).data('name');
      openConversation(id,name);
    });
  }).fail(function(jqXHR){
    console.error('Failed to load users', jqXHR.status, jqXHR.responseText);
    // Try a graceful fallback: load public consultants list (visible to clients)
    $.getJSON('../api/consultants.php?action=list').done(function(list){
      if(Array.isArray(list) && list.length){
        const data = list.map(c=>({ id: c.user_id, consultant_id: c.consultant_id, name: c.name, email: c.email || '', expertise: c.expertise || '', bio: c.bio || '', pic: c.pic || ('https://ui-avatars.com/api/?name='+encodeURIComponent(c.name)) }));
        const html = data.map(u=>`<div class="conv-user" data-id="${u.id}" data-name="${safeHtml(u.name)}"><div class="avatar">${safeHtml(u.name.charAt(0)||'U')}</div><div><div class="conv-name">${safeHtml(u.name)}</div><div class="conv-snippet text-muted small">${safeHtml(u.expertise||u.email||'')}</div></div></div>`).join('');
        $('#recipient-list').html(html);
        $('#recipient-list .conv-user').on('click', function(){ const id = $(this).data('id'); const name = $(this).data('name'); openConversation(id,name); });
        return;
      }
      showError('Failed to load users. Please sign in or try again.');
    }).fail(function(){
      showError('Failed to load users. Please sign in or try again.');
    });
  });
}

function loadInbox(){
  $('#conv-list').html('<div class="text-center p-3 text-secondary">Loading...</div>');
  $.getJSON('../api/messages.php?action=inbox').done(function(resp){
    if(!resp || !resp.success){ $('#conv-list').html('<div class="p-3 text-secondary">No conversations</div>'); return; }
    const convos = resp.conversations || [];
    if(convos.length===0){ $('#conv-list').html('<div class="p-3 text-secondary">No conversations yet</div>'); return; }
    const html = convos.map(c=>{
      const other = c.partner_name || c.partner_id || 'User';
      const snippet = c.content ? c.content.substring(0,60) : '';
      return `<div class="conv-user" data-id="${c.partner_id}" data-name="${safeHtml(other)}"><div class="avatar">${safeHtml((other||'U').charAt(0))}</div><div style="flex:1"><div class="conv-name">${safeHtml(other)}</div><div class="conv-snippet">${safeHtml(snippet)}</div></div><div class="conv-meta text-end"><div class="conv-time small text-muted">${c.sent_at?c.sent_at:''}</div></div></div>`;
    }).join('');
    $('#conv-list').html(html);
    $('#conv-list .conv-user').on('click', function(){
      const id = $(this).data('id');
      const name = $(this).data('name');
      openConversation(id,name);
    });
  }).fail(function(){ $('#conv-list').html('<div class="p-3 text-danger">Error loading conversations</div>'); });
}

function openConversation(userId, name){
  currentChatUserId = userId; currentChatName = name || '';
  $('#messages-header').text(currentChatName || 'Conversation');
  $('#msg-compose').show(); $('#send-btn').prop('disabled', false);
  loadThread(userId);
}

function loadThread(userId){
  $('#message-list').html('<div class="text-center p-3 text-secondary">Loading messages...</div>');
  $.getJSON('../api/messages.php?action=thread&user_id='+encodeURIComponent(userId)).done(function(resp){
    if(!resp || !resp.success){ $('#message-list').html('<div class="p-3 text-secondary">Could not load messages</div>'); return; }
    const msgs = resp.messages || [];
    if(msgs.length===0){ $('#message-list').html('<div class="p-3 text-secondary">No messages yet. Say hello!</div>'); return; }
    const me = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;
    const html = msgs.map(m=>{
      const cls = (m.sender_id==me)?'bubble me':'bubble them';
      const time = m.sent_at ? m.sent_at : '';
      return `<div class="${cls}">${safeHtml(m.content)}<span class="meta">${safeHtml(time)}</span></div>`;
    }).join('');
    $('#message-list').html(html);
    // scroll to bottom
    const ml = document.getElementById('message-list'); ml.scrollTop = ml.scrollHeight;
  }).fail(function(jqXHR){ showError('Failed to load messages'); });
}

$('#send-msg-form').on('submit', function(e){
  e.preventDefault();
  const content = $('#msg-input').val().trim();
  if(!content){ showError('Please type a message before sending.'); return; }
  if(!currentChatUserId){ showError('No recipient selected. Please choose a conversation or recipient.'); return; }
  console.log('Sending message', { recipient_id: currentChatUserId, content });
  $('#send-btn').prop('disabled', true);
  $.post('../api/messages.php?action=send', {recipient_id: currentChatUserId, content: content}).done(function(resp){
    console.log('send response', resp);
    if(resp && resp.success){ $('#msg-input').val(''); loadThread(currentChatUserId); loadInbox(); }
    else{ showError((resp && resp.error) ? resp.error : 'Failed to send'); }
  }).fail(function(jqXHR){
    console.error('send failed', jqXHR.status, jqXHR.responseText);
    if (jqXHR.responseText && jqXHR.responseText.indexOf('<!DOCTYPE')===0) { showError('Session expired. Please sign in.'); }
    else if (jqXHR.responseText) { try { const j = JSON.parse(jqXHR.responseText); showError(j.error || 'Server error'); } catch(e){ showError('Network/server error sending message'); } }
    else showError('Network/server error sending message');
  }).always(function(){ $('#send-btn').prop('disabled', false); });
});

// initial
loadUsers(); loadInbox();

</script>
</body>
</html>
