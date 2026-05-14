<script>
const app = {
    role: null, online: true, afkTimer: null, pollTimer: null, hbTimer: null,
    
    init: function() {
        const isLogged = <?= $isLoggedIn ? 'true' : 'false' ?>;
        if(isLogged) this.completeLogin("<?= $userRole ?>");
    },

    login: async function() {
        const u = document.getElementById('login-user').value;
        const p = document.getElementById('login-pass').value;
        const err = document.getElementById('login-error');
        if(!u || !p) { err.innerText = "Fields required"; err.style.display="block"; return; }
        
        let fd = new FormData(); fd.append('action', 'login'); fd.append('username', u); fd.append('password', p);
        try {
            const res = await fetch('index.php?route=/api/auth', { method: 'POST', body: fd });
            const data = await res.json();
            if(data.status === 'success') { err.style.display="none"; this.completeLogin(data.role); }
            else { err.innerText = data.message; err.style.display="block"; }
        } catch(e) { err.innerText = "Network Error"; err.style.display="block"; }
    },
    
    completeLogin: function(role) {
        this.role = role;
        document.getElementById('my-role-display').innerText = role;
        document.getElementById('profile-role').innerText = role;
        document.getElementById('profile-avatar').innerText = role.charAt(0);
        
        document.getElementById('view-login').classList.remove('active');
        document.getElementById('bottom-nav').style.display = 'flex';
        if(role === 'Admin') document.getElementById('nav-admin').style.display = 'flex';
        
        this.switchTab('view-home'); // Default to directory now
        this.initAFK(); this.pollData();
        this.pollTimer = setInterval(() => this.pollData(), 2000);
        this.hbTimer = setInterval(() => { if(this.online) fetch('index.php?route=/api/auth&action=heartbeat'); }, 30000);
    },

    switchTab: function(id) {
        document.querySelectorAll('.view').forEach(v => { if(v.id !== 'view-login') v.classList.remove('active'); });
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        document.querySelector(`.nav-item[data-target="${id}"]`).classList.add('active');
        if(id === 'view-chat') this.scroll();
    },

    pollData: async function() {
        if(!this.role) return;
        
        // Fetch Users for Directory
        try {
            const uRes = await fetch('index.php?route=/api/chat&action=users'); 
            const uData = await uRes.json();
            if(uData.status === 'success') {
                let html = '';
                uData.data.forEach(u => {
                    if(u.role === this.role) return; // Skip self
                    const isOnline = u.status === 'online';
                    html += `
                        <div class="directory-card" onclick="app.switchTab('view-chat')">
                            <div class="dir-info">
                                <div class="dir-avatar">${u.role.charAt(0)}</div>
                                <div>
                                    <strong style="color:var(--accent)">${u.role}</strong>
                                    <div style="font-size:0.8rem; color:var(--text-muted)">
                                        <span class="status-dot ${isOnline ? '' : 'offline'}" style="display:inline-block; margin-right:5px;"></span>
                                        ${isOnline ? 'Active Now' : 'Offline'}
                                    </div>
                                </div>
                            </div>
                            <span style="font-size:1.2rem;">💬</span>
                        </div>
                    `;
                });
                document.getElementById('directory-list').innerHTML = html || '<div style="text-align:center; color:var(--text-muted); margin-top:2rem;">No visible users.</div>';
            }
        } catch(e) {}

        // Fetch Chat Messages
        try {
            const mRes = await fetch('index.php?route=/api/chat&action=fetch'); 
            const mData = await mRes.json();
            if(mData.status === 'success') {
                const cb = document.getElementById('chat-messages'); 
                const bottom = cb.scrollHeight - cb.scrollTop <= cb.clientHeight + 50;
                cb.innerHTML = '<div style="text-align:center; font-size:0.8rem; color:var(--text-muted); margin-bottom:10px;">Enterprise AES-256 Enabled</div>' + 
                mData.data.map(m => `<div class="message msg-${m.sender_role===this.role?'sent':'recv'}"><div style="font-size:0.7rem;opacity:0.7">${m.sender_role}</div>${m.text}</div>`).join('');
                if(bottom) this.scroll();
            }
        } catch(e) {}
    },

    send: async function() {
        const i = document.getElementById('msg-input'); const v = i.value.trim(); if(!v) return; i.value = '';
        let fd = new FormData(); fd.append('action', 'send'); fd.append('message', v); fd.append('type', 'text');
        await fetch('index.php?route=/api/chat', {method:'POST', body:fd}); this.pollData(); this.scroll();
    },
    
    sendFile: async function() {
        const txt = document.getElementById('snip-txt').value; const ext = document.getElementById('snip-ext').value; if(!txt) return;
        let fd = new FormData(); fd.append('content', txt); fd.append('extension', ext);
        const res = await fetch('index.php?route=/api/file', {method:'POST', body:fd}); const data = await res.json();
        if(data.status==='success') {
            let msgFd = new FormData(); msgFd.append('action', 'send'); msgFd.append('type', 'file_snippet');
            msgFd.append('message', `File Generated:<br><a href="${data.url}" download class="file-card">📄 Download ${data.filename}</a>`);
            await fetch('index.php?route=/api/chat', {method:'POST', body:msgFd});
            document.getElementById('snippet-modal').classList.remove('active'); document.getElementById('snip-txt').value=''; this.pollData();
        } else { alert(data.message); }
    },

    createUser: async function() {
        let fd = new FormData(); fd.append('action', 'create_user'); fd.append('new_username', document.getElementById('new-u').value); fd.append('new_password', document.getElementById('new-p').value); fd.append('new_role', document.getElementById('new-r').value);
        const res = await fetch('index.php?route=/api/auth', {method:'POST', body:fd}); const data = await res.json();
        document.getElementById('admin-msg').innerText = data.message; document.getElementById('admin-msg').style.color = data.status==='success'?'var(--online)':'#ff4757';
    },
    
    changePw: async function() {
        let fd = new FormData(); fd.append('action', 'change_password'); fd.append('old_password', document.getElementById('pw-old').value); fd.append('new_password', document.getElementById('pw-new').value);
        const res = await fetch('index.php?route=/api/auth', {method:'POST', body:fd}); const data = await res.json();
        document.getElementById('pw-msg').innerText = data.message; document.getElementById('pw-msg').style.color = data.status==='success'?'var(--online)':'#ff4757';
    },

    initAFK: function() {
        const rst = () => {
            if(!this.online) { this.online = true; document.getElementById('my-status-dot').className="status-dot"; document.getElementById('my-status-text').innerText="Online"; fetch('index.php?route=/api/auth&action=heartbeat'); }
            clearTimeout(this.afkTimer);
            this.afkTimer = setTimeout(() => { this.online = false; document.getElementById('my-status-dot').className="status-dot offline"; document.getElementById('my-status-text').innerText="AFK"; }, 120000);
        };
        ['mousemove','keypress','click','scroll'].forEach(e => window.addEventListener(e, rst)); rst();
    },
    logout: async function() { await fetch('index.php?route=/api/auth&action=logout'); window.location.reload(); },
    scroll: function() { const b = document.getElementById('chat-messages'); b.scrollTop = b.scrollHeight; }
};
window.onload = () => app.init();
</script>