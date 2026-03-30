@php
    $serverAuth = auth()->check() ? '1' : '0';
@endphp

<button id="__chikin_msg_btn" title="Messages" data-server-auth="{{ $serverAuth }}"
    style="position:fixed;right:18px;bottom:18px;z-index:10000;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:999px;background:var(--dirty-white,#fff4e6);color:var(--text-dark,#42210b);box-shadow:0 8px 20px rgba(66,33,11,0.06);border:none;cursor:pointer;padding:0;font-family:inherit;transition:all 0.3s ease;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.9L3 20l1.1-3.3A7.972 7.972 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
</button>

<!-- Messaging Modal -->
<div id="__chikin_msg_modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10001;justify-content:flex-end;align-items:flex-end;padding:20px;box-sizing:border-box;font-family:inherit">
    <div style="display:flex;flex-direction:column;background:white;border-radius:12px;width:100%;max-width:500px;max-height:80vh;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <!-- Modal Header -->
        <div style="padding:16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:18px;font-weight:600">Messages</h3>
            <button id="__chikin_msg_close" style="background:none;border:none;font-size:24px;cursor:pointer;color:#999">×</button>
        </div>

        <!-- Modal Content -->
        <div style="flex:1;display:flex;overflow:hidden">
            <!-- Users List -->
            <div id="__chikin_msg_users_list" style="width:150px;background:#f8fafc;border-right:1px solid #e6eef8;padding:8px;overflow:auto">
                <ul style="list-style:none;padding:0;margin:0" id="__chikin_userList">
                    <li style="padding:8px;color:#999;font-size:13px">Loading...</li>
                </ul>
            </div>

            <!-- Conversation Pane -->
            <div style="flex:1;display:flex;flex-direction:column;background:#f6f9fc">
                <!-- Conversation Header -->
                <div id="__chikin_msg_header" style="padding:12px;border-bottom:1px solid #eee;background:#fff;font-weight:600;font-size:14px">
                    Select a user to chat
                </div>

                <!-- Messages -->
                <div id="__chikin_msg_pane" style="flex:1;padding:12px;overflow:auto;display:flex;flex-direction:column;gap:10px">
                    <div style="color:#999;font-size:13px;text-align:center;padding:20px 0">Select a user to start messaging</div>
                </div>

                <!-- Message Input -->
                <div style="padding:12px;border-top:1px solid #eee;background:#fff">
                    <form id="__chikin_msg_form" style="display:none">
                        <input type="hidden" id="__chikin_to_user_id" />
                        <div style="display:flex;gap:8px">
                            <textarea id="__chikin_msg_body" placeholder="Type a message..." style="flex:1;padding:8px;border:1px solid #dfe6ef;border-radius:6px;font-size:13px;resize:none;height:60px;font-family:inherit"></textarea>
                            <button type="submit" style="background:#2563eb;color:white;padding:8px 12px;border-radius:6px;border:none;cursor:pointer;font-weight:500;white-space:nowrap;font-family:inherit">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.__initMessagingButton = function(){
        try {
            const btn = document.getElementById('__chikin_msg_btn');
            const modal = document.getElementById('__chikin_msg_modal');
            const closeBtn = document.getElementById('__chikin_msg_close');
            const userList = document.getElementById('__chikin_userList');
            const msgPane = document.getElementById('__chikin_msg_pane');
            const convHeader = document.getElementById('__chikin_msg_header');
            const toUserInput = document.getElementById('__chikin_to_user_id');
            const msgForm = document.getElementById('__chikin_msg_form');
            const msgBody = document.getElementById('__chikin_msg_body');
            const currentUserId = window.Laravel?.userId || null;
            let activeConversationId = null;
            let pollTimer = null;

            if(!btn || !modal) {
                console.warn('Messaging elements not found');
                return false;
            }

            console.log('Initializing messaging button...');

            const serverAuth = btn.getAttribute('data-server-auth') === '1';
            function hasClientUser(){
                try { return !!JSON.parse(localStorage.getItem('user') || 'null') } catch(e){ return false }
            }

            const currentPath = window.location.pathname || '/';
            const isPanelPath = (p => {
                const panelPrefixes = ['/admin-panel', '/manager-panel', '/manager/', '/staff-panel', '/staff/', '/hr-panel', '/super-admin-panel', '/custom-panel', '/supplier-panel'];
                return panelPrefixes.some(x => p === x || p.startsWith(x));
            })(currentPath);

            const isAuthenticated = serverAuth || hasClientUser();
            const show = isPanelPath && isAuthenticated;
            
            console.log('Messaging button visibility check:', {
                path: currentPath,
                isPanelPath: isPanelPath,
                serverAuth: serverAuth,
                hasClientUser: hasClientUser(),
                isAuthenticated: isAuthenticated,
                show: show
            });

            if(!show) { 
                btn.style.display = 'none'; 
                console.log('Hiding messaging button');
                return false; 
            }

            console.log('Showing messaging button');

            function htmlEscape(s){ return (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
            function roleLabel(r){ return (r || 'User').toString().replace(/_/g, ' '); }

            btn.addEventListener('click', async () => {
                modal.style.display = 'flex';
                await loadUsers();
            });

            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                stopPolling();
                activeConversationId = null;
            });

            modal.addEventListener('click', (e) => {
                if(e.target === modal) {
                    modal.style.display = 'none';
                    stopPolling();
                    activeConversationId = null;
                }
            });

            async function loadUsers(){
                try {
                    const res = await fetch('/api/hr/messages/users', {headers:{'X-Requested-With':'XMLHttpRequest'}});
                    if(!res.ok) { userList.innerHTML = '<li style="color:red;padding:8px;font-size:12px">Error loading users</li>'; return; }
                    const data = await res.json();
                    userList.innerHTML = '';
                    if(!data.users || data.users.length === 0) {
                        userList.innerHTML = '<li style="color:#999;padding:8px;font-size:12px">No users available</li>';
                        return;
                    }
                    data.users.forEach(u => {
                        const li = document.createElement('li');
                        li.setAttribute('data-id', u.id);
                        li.setAttribute('data-name', u.name);
                        li.setAttribute('data-role', u.role || 'User');
                        li.style.cssText = 'padding:8px;margin-bottom:4px;background:white;border-radius:4px;cursor:pointer;border:1px solid #eef2f7;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap';
                        li.textContent = u.name;
                        li.addEventListener('click', () => selectUser(u.id, u.name, u.role || 'User'));
                        userList.appendChild(li);
                    });
                } catch(e) { userList.innerHTML = '<li style="color:red;padding:8px;font-size:12px">Error</li>'; }
            }

            function selectUser(userId, name, role){
                activeConversationId = userId;
                toUserInput.value = userId;
                convHeader.textContent = 'Chat with ' + name + ' (' + roleLabel(role) + ')';
                msgForm.style.display = 'block';
                msgBody.value = '';
                loadConversation(userId);
                startPolling();
                msgBody.focus();
            }

            async function loadConversation(userId){
                msgPane.innerHTML = '<em style="color:#999;text-align:center">Loading...</em>';
                try {
                    const res = await fetch('/api/hr/messages/conversation/' + userId, {headers:{'X-Requested-With':'XMLHttpRequest'}});
                    if(!res.ok) {
                        const errorData = await res.json().catch(() => ({}));
                        msgPane.innerHTML = '<div style="color:#c53030;font-size:13px">' + htmlEscape(errorData.error || 'Error loading messages') + '</div>';
                        return;
                    }
                    const data = await res.json();
                    msgPane.innerHTML = '';
                    if(!data.messages || data.messages.length === 0) {
                        msgPane.innerHTML = '<div style="color:#999;font-size:13px;text-align:center">No messages yet. Start the conversation!</div>';
                    } else {
                        data.messages.forEach(m => {
                            const mine = currentUserId !== null && String(m.from_user_id) === String(currentUserId);
                            const div = document.createElement('div');
                            div.style.cssText = 'margin-bottom:10px;max-width:85%;padding:8px 10px;border-radius:8px;background:' + (mine ? '#dcfce7' : '#fff') + ';align-self:' + (mine ? 'flex-end' : 'flex-start') + ';margin-left:' + (mine ? 'auto' : '0') + ';margin-right:' + (mine ? '0' : 'auto') + ';word-break:break-word;font-size:13px';
                            const sender = m.from_user && m.from_user.name ? (htmlEscape(m.from_user.name) + ' (' + htmlEscape(roleLabel(m.from_user.role)) + ')') : 'User';
                            div.innerHTML = (mine ? '' : '<div style="font-size:11px;color:#334155;font-weight:600;margin-bottom:4px">' + sender + '</div>') + '<div style="color:#111">' + htmlEscape(m.body) + '</div>' + '<div style="font-size:11px;color:#666;margin-top:4px">' + new Date(m.created_at).toLocaleString() + '</div>';
                            msgPane.appendChild(div);
                        });
                    }
                    msgPane.scrollTop = msgPane.scrollHeight;
                } catch(e) { msgPane.innerHTML = '<div style="color:#c53030;font-size:13px">Error: ' + htmlEscape(e.message) + '</div>'; }
            }

            function startPolling(){
                stopPolling();
                pollTimer = setInterval(async () => {
                    if(!activeConversationId) return;
                    await loadConversation(activeConversationId);
                }, 3000);
            }

            function stopPolling(){
                if(pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            }

            function getCsrfToken(){
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            }

            msgForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const body = msgBody.value.trim();
                if(!body) return;
                try {
                    const csrf = getCsrfToken();
                    const res = await fetch('/api/hr/messages/send', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With':'XMLHttpRequest',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({to_user_id: toUserInput.value, body: body})
                    });
                    if(!res.ok) { const err = await res.json().catch(() => ({})); alert('Error: ' + (err.error || 'Failed to send')); return; }
                    msgBody.value = '';
                    msgBody.focus();
                    await loadConversation(activeConversationId);
                } catch(e) { alert('Error: ' + e.message); }
            });
            
            return true;
        } catch(e){
            console.error('Messaging button init error:', e);
            return false;
        }
    };

    // Initialize when DOM is ready
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', window.__initMessagingButton);
    } else {
        window.__initMessagingButton();
    }
</script>
