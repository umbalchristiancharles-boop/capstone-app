@php
        $serverAuth = auth()->check() ? '1' : '0';
@endphp

<a id="__chikin_msg_btn" href="/hr/messages" title="Messages" data-server-auth="{{ $serverAuth }}"
     style="position:fixed;right:18px;bottom:18px;z-index:9999;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:999px;background:#2563eb;color:#fff;box-shadow:0 6px 18px rgba(37,99,235,0.2);text-decoration:none">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.9L3 20l1.1-3.3A7.972 7.972 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
</a>

<script>
    (function(){
        try {
            const btn = document.getElementById('__chikin_msg_btn')
            if(!btn) return

            // If server-side already says not-auth, check client-side localStorage user
            const serverAuth = btn.getAttribute('data-server-auth') === '1'

            function hasClientUser(){
                try { return !!JSON.parse(localStorage.getItem('user') || 'null') } catch(e){ return false }
            }

            const isPanelPath = (p => {
                const panels = ['/admin-panel','/manager-panel','/staff-panel','/hr-panel','/super-admin-panel']
                return panels.some(x => p === x || p.startsWith(x + '/'))
            })(window.location.pathname || '/')

            // Only show when on a panel path AND authenticated either server-side or client-side
            const show = isPanelPath && (serverAuth || hasClientUser())
            if(!show) btn.style.display = 'none'
        } catch(e){}
    })();
</script>
