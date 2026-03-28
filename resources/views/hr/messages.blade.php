@extends('layouts.app')

@section('content')
<div id="chatRoot" style="display:flex;height:80vh;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden">
    <div id="users" style="width:260px;background:#f8fafc;border-right:1px solid #e6eef8;padding:12px;overflow:auto">
        <h3 style="margin:6px 0 12px;font-size:16px">Branch Users</h3>
        <ul style="list-style:none;padding:0;margin:0" id="userList">
            @foreach($users as $u)
                <li data-id="{{ $u->id }}" data-role="{{ $u->role ?? 'User' }}" style="padding:8px;margin-bottom:6px;background:white;border-radius:4px;cursor:pointer;border:1px solid #eef2f7">{{ $u->name }}</li>
            @endforeach
        </ul>
    </div>

    <div style="flex:1;display:flex;flex-direction:column">
        <div id="conversationHeader" style="padding:12px;border-bottom:1px solid #eee;background:#fff">
            <strong id="convWith">Select a user to chat</strong>
        </div>

        <div id="messagesPane" style="flex:1; padding:12px; overflow:auto; background:#f6f9fc">
            <div id="messages" style="display:flex;flex-direction:column;gap:10px"></div>
        </div>

        <div style="padding:12px;border-top:1px solid #eee;background:#fff">
            <form id="sendForm">
                <input type="hidden" id="to_user_id" name="to_user_id" />
                <textarea id="body" name="body" placeholder="Write a message..." style="width:100%;height:70px;padding:8px;border:1px solid #dfe6ef;border-radius:6px"></textarea>
                <div style="text-align:right;margin-top:6px">
                    <button type="submit" style="background:#2563eb;color:white;padding:8px 14px;border-radius:6px;border:none">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const chatRoot = document.getElementById('chatRoot');
    const userList = document.getElementById('userList');
    const messagesDiv = document.getElementById('messages');
    const convWith = document.getElementById('convWith');
    const toUserInput = document.getElementById('to_user_id');
    const sendForm = document.getElementById('sendForm');
    const currentUserId = "{{ auth()->id() }}" || null;
    let activeConversationId = null;
    let pollTimer = null;

    function htmlEscape(s){ return (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function roleLabel(r){ return (r || 'User').toString().replace(/_/g, ' '); }

    userList.addEventListener('click', async function(e){
        const li = e.target.closest('li[data-id]');
        if(!li) return;
        const id = li.getAttribute('data-id');
        activeConversationId = id;
        toUserInput.value = id;
        const role = li.getAttribute('data-role') || 'User';
        convWith.textContent = 'Conversation with ' + li.textContent.trim() + ' (' + roleLabel(role) + ')';
        await loadConversation(id);
        startPolling();
    });

    async function loadConversation(userId){
        messagesDiv.innerHTML = '<em>Loading...</em>';
        const res = await fetch('/api/hr/messages/conversation/' + userId, {headers:{'X-Requested-With':'XMLHttpRequest'}});
        if(!res.ok){ 
            const errorData = await res.json().catch(() => ({}));
            const errorMsg = errorData.error || `Error: ${res.statusText}`;
            messagesDiv.innerHTML = '<div style="color:#c53030">' + htmlEscape(errorMsg) + '</div>'; 
            return; 
        }
        const data = await res.json();
        messagesDiv.innerHTML = '';
        data.messages.forEach(m => {
            const mine = currentUserId !== null && String(m.from_user_id) === String(currentUserId);
            const el = document.createElement('div');
            el.style.marginBottom = '10px';
            el.style.maxWidth = '75%';
            el.style.padding = '8px 10px';
            el.style.borderRadius = '8px';
            el.style.background = mine ? '#dcfce7' : '#fff';
            el.style.alignSelf = mine ? 'flex-end' : 'flex-start';
            el.style.marginLeft = mine ? 'auto' : '0';
            el.style.marginRight = mine ? '0' : 'auto';
            el.style.wordBreak = 'break-word';
            const sender = m.from_user && m.from_user.name ? (htmlEscape(m.from_user.name) + ' (' + htmlEscape(roleLabel(m.from_user.role)) + ')') : 'User';
            el.innerHTML = (mine ? '' : '<div style="font-size:11px;color:#334155;font-weight:600;margin-bottom:5px">' + sender + '</div>') +
                '<div style="font-size:13px;color:#111">' + htmlEscape(m.body) + '</div>' +
                '<div style="font-size:11px;color:#666;margin-top:4px">' + new Date(m.created_at).toLocaleString() + '</div>';
            messagesDiv.appendChild(el);
        });
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function startPolling(){
        stopPolling();
        pollTimer = setInterval(async () => {
            if(!activeConversationId) return;
            await loadConversation(activeConversationId);
        }, 3000);
    }

    function stopPolling(){
        if(pollTimer){
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    window.addEventListener('beforeunload', stopPolling);

    sendForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const to = toUserInput.value;
        const body = document.getElementById('body').value.trim();
        if(!to || !body) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch('/api/hr/messages/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({to_user_id: to, body: body})
        });
        if(!res.ok){ 
            const errorData = await res.json().catch(() => ({}));
            const errorMsg = errorData.error || `Error: ${res.statusText}`;
            alert('Send failed: ' + errorMsg); 
            return; 
        }
        document.getElementById('body').value = '';
        await loadConversation(to);
    });
</script>

@endsection
