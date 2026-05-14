<script>
const app = {
    role: null, online: true, afkTimer: null, pollTimer: null, hbTimer: null,
    
    // V2 Optimization Variables
    lastMsgId: 0,
    currentPollInterval: 2000,
    minPollInterval: 2000,
    maxPollInterval: 15000,
    
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
        
        if(role.toLowerCase() === 'admin') {
            document.getElementById('nav-admin').style.display = 'flex';
        }
        
        this.switchTab('view-home');
        this.initAFK(); 
        
        // V2: Start Recursive Polling instead of Interval
        this.pollData();
        
        this.hbTimer = setInterval(() => { if(this.online) fetch('index.php?route=/api/auth&action=heartbeat'); }, 30000);
    },

    switchTab: function(id) {
        document.querySelectorAll('.view').forEach(v => { if(v.id !== 'view-login') v.classList.remove('active'); });
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        
        const navTarget = document.querySelector(`.nav-item[data-target="${id}"]`);
        if(navTarget) navTarget.classList.add('active');
        
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
                    if(u.role === this.role) return;
                    const isOnline = u.status === 'online';
                    html += `
                        <div class="directory-card" onclick="app.switchTab('view-chat')">
                            <div class="dir-info">
                                <div class="dir-avatar">${u.role.charAt(0)}</div>
                                <div>
                                    <strong style="color:var(--accent); letter-spacing:1px;">${u.role}</strong>
                                    <div style="font-size:0.8rem; color:var(--text-muted)">
                                        <span class="status-dot ${isOnline ? '' : 'offline'}" style="display:inline-block; margin-right:5px;"></span>
                                        ${isOnline ? 'Active' : 'Offline'}
                                    </div>
                                </div>
                            </div>
                            <span style="font-size:1.2rem; color:var(--accent);">➔</span>
                        </div>
                    `;
                });
                document.getElementById('directory-list').innerHTML = html || '<div style="text-align:center; color:var(--text-muted); margin-top:2rem;">No users available in network.</div>';
            }
        } catch(e) {}

        // V2: Fetch Chat Messages with Delta Caching (last_id)
        try {
            const mRes = await fetch(`index.php?route=/api/chat&action=fetch&last_id=${this.lastMsgId}`); 
            const mData = await mRes.json();
            if(mData.status === 'success') {
                
                if(mData.data.length > 0) {
                    const cb = document.getElementById('chat-messages'); 
                    const bottom = cb.scrollHeight - cb.scrollTop <= cb.clientHeight + 50;
                    
                    // Initial load visual wrapper
                    if(this.lastMsgId === 0) {
                        cb.innerHTML = '<div style="text-align:center; font-size:0.8rem; color:var(--accent); margin-bottom:10px; text-transform:uppercase; letter-spacing: 2px;">Secured Channel</div>';
                    }

                    // Append ONLY new messages
                    const newHtml = mData.data.map(m => `<div class="message msg-${m.sender_role===this.role?'sent':'recv'}"><div style="font-size:0.7rem;opacity:0.7;margin-bottom:3px;">${m.sender_role}</div>${m.text}</div>`).join('');
                    cb.innerHTML += newHtml;
                    
                    // Update cache state & snap polling speed back to MAX
                    this.lastMsgId = Math.max(...mData.data.map(m => parseInt(m.id)));
                    this.currentPollInterval = this.minPollInterval; 
                    
                    if(bottom) this.scroll();
                } else {
                    // Smart Backoff: Decay polling speed if chat is quiet (Saves Server Load)
                    this.currentPollInterval = Math.min(this.maxPollInterval, this.currentPollInterval + 2000);
                }
            }
        } catch(e) {}

        // Schedule next poll using dynamic interval
        this.pollTimer = setTimeout(() => this.pollData(), this.currentPollInterval);
    },

    send: async function() {
        const i = document.getElementById('msg-input'); const v = i.value.trim(); if(!v) return; i.value = '';
        let fd = new FormData(); fd.append('action', 'send'); fd.append('message', v); fd.append('type', 'text');
        
        // V2: Snap polling to fast mode instantly on user action
        this.currentPollInterval = this.minPollInterval;
        
        await fetch('index.php?route=/api/chat', {method:'POST', body:fd}); 
        clearTimeout(this.pollTimer); this.pollData(); this.scroll();
    },
    
    sendFile: async function() {
        const txt = document.getElementById('snip-txt').value; const ext = document.getElementById('snip-ext').value; if(!txt) return;
        let fd = new FormData(); fd.append('content', txt); fd.append('extension', ext);
        const res = await fetch('index.php?route=/api/file', {method:'POST', body:fd}); const data = await res.json();
        if(data.status==='success') {
            let msgFd = new FormData(); msgFd.append('action', 'send'); msgFd.append('type', 'file_snippet');
            msgFd.append('message', `System Code Artifact Generated:<br><a href="${data.url}" download class="file-card">📦 Extract ${data.filename}</a>`);
            
            this.currentPollInterval = this.minPollInterval;
            await fetch('index.php?route=/api/chat', {method:'POST', body:msgFd});
            
            document.getElementById('snippet-modal').classList.remove('active'); document.getElementById('snip-txt').value=''; 
            clearTimeout(this.pollTimer); this.pollData();
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
            if(!this.online) { 
                this.online = true; 
                this.currentPollInterval = this.minPollInterval; // Wake up polling
                document.getElementById('my-status-dot').className="status-dot"; 
                document.getElementById('my-status-text').innerText="Online"; 
                fetch('index.php?route=/api/auth&action=heartbeat'); 
            }
            clearTimeout(this.afkTimer);
            this.afkTimer = setTimeout(() => { 
                this.online = false; 
                this.currentPollInterval = this.maxPollInterval; // Slow down polling drastically when AFK
                document.getElementById('my-status-dot').className="status-dot offline"; 
                document.getElementById('my-status-text').innerText="AFK"; 
            }, 120000);
        };
        ['mousemove','keypress','click','scroll','touchstart'].forEach(e => window.addEventListener(e, rst, {passive: true})); rst();
    },
    logout: async function() { await fetch('index.php?route=/api/auth&action=logout'); window.location.reload(); },
    scroll: function() { const b = document.getElementById('chat-messages'); b.scrollTop = b.scrollHeight; }
};
window.onload = () => app.init();
</script>