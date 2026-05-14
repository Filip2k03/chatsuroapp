<script>
    const app = {
        currentUser: null,
        isOnline: true,
        afkTimer: null,
        pollInterval: null,
        heartbeatInterval: null,
        AFK_LIMIT: 120000, 

        init: function() {
            // PHP injects initial session state
            const isLogged = <?= $isLoggedIn ? 'true' : 'false' ?>;
            const role = "<?= $userRole ?>";
            
            if(isLogged && role) {
                this.completeLogin(role);
            }
        },

        login: async function() {
            const userBox = document.getElementById('login-user');
            const passBox = document.getElementById('login-pass');
            const errorMsg = document.getElementById('login-error');
            const authBtn = document.querySelector('#view-login button');
            
            if (!userBox.value || !passBox.value) {
                errorMsg.innerText = "Error: Fields required.";
                errorMsg.style.display = 'block';
                return;
            }

            authBtn.innerText = 'Authenticating...';
            errorMsg.style.display = 'none';
            
            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('username', userBox.value);
                formData.append('password', passBox.value);

                // Re-routed to the new API path
                const res = await fetch('index.php?route=/api/auth', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.status === 'success') {
                    this.completeLogin(data.role);
                } else {
                    errorMsg.innerText = data.message;
                    errorMsg.style.display = 'block';
                }
            } catch (e) {
                errorMsg.innerText = "Network Error.";
                errorMsg.style.display = 'block';
            }
            authBtn.innerText = 'Authenticate';
        },

        completeLogin: function(role) {
            this.currentUser = role;
            
            document.getElementById('my-role-display').innerText = role;
            document.getElementById('profile-name').innerText = role;
            document.getElementById('profile-role').innerText = role;
            document.getElementById('profile-avatar').innerText = role.charAt(0);
            
            document.getElementById('view-login').classList.remove('active');
            document.getElementById('bottom-nav').style.display = 'flex';
            
            if (role === 'Admin') {
                document.getElementById('nav-admin').style.display = 'flex';
            }
            
            this.switchTab('view-chat'); 
            this.isOnline = true;
            this.initAFKTracking();
            this.startPolling();
        },

        changePassword: async function() {
            const btn = document.querySelector('#view-settings button[onclick="app.changePassword()"]');
            const msgBox = document.getElementById('pw-msg');
            const oldPw = document.getElementById('pw-old').value;
            const newPw = document.getElementById('pw-new').value;

            if (!oldPw || !newPw) {
                msgBox.style.color = '#ff4757';
                msgBox.innerText = 'Both fields required.';
                msgBox.style.display = 'block';
                return;
            }

            btn.innerText = "Rotating...";
            
            const fd = new FormData();
            fd.append('action', 'change_password');
            fd.append('old_password', oldPw);
            fd.append('new_password', newPw);

            try {
                const res = await fetch('index.php?route=/api/auth', { method: 'POST', body: fd });
                const data = await res.json();
                
                msgBox.style.display = 'block';
                if (data.status === 'success') {
                    msgBox.style.color = 'var(--online)';
                    msgBox.innerText = data.message;
                    document.getElementById('pw-old').value = '';
                    document.getElementById('pw-new').value = '';
                } else {
                    msgBox.style.color = '#ff4757';
                    msgBox.innerText = data.message;
                }
            } catch(e) {
                msgBox.style.color = '#ff4757';
                msgBox.innerText = 'Network error during rotation.';
                msgBox.style.display = 'block';
            }
            btn.innerText = "Rotate Password";
        },

        createUser: async function() {
            const btn = document.querySelector('#view-admin button');
            const msgBox = document.getElementById('admin-msg');
            const u = document.getElementById('new-user').value;
            const p = document.getElementById('new-pass').value;
            const r = document.getElementById('new-role').value;

            if(!u || !p) {
                msgBox.style.color = '#ff4757';
                msgBox.innerText = 'Username and Password required.';
                msgBox.style.display = 'block';
                return;
            }

            btn.innerText = "Provisioning...";
            
            const fd = new FormData();
            fd.append('action', 'create_user');
            fd.append('new_username', u);
            fd.append('new_password', p);
            fd.append('new_role', r);

            try {
                const res = await fetch('index.php?route=/api/auth', { method: 'POST', body: fd });
                const data = await res.json();
                
                msgBox.style.display = 'block';
                if(data.status === 'success') {
                    msgBox.style.color = 'var(--online)';
                    msgBox.innerText = data.message;
                    document.getElementById('new-user').value = '';
                    document.getElementById('new-pass').value = '';
                } else {
                    msgBox.style.color = '#ff4757';
                    msgBox.innerText = data.message;
                }
            } catch(e) {
                msgBox.style.color = '#ff4757';
                msgBox.innerText = 'Network error during provisioning.';
                msgBox.style.display = 'block';
            }
            btn.innerText = "Create User";
        },

        switchTab: function(targetId) {
            document.querySelectorAll('.view').forEach(v => {
                if(v.id !== 'view-login') v.classList.remove('active');
            });
            
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.querySelector(`.nav-item[data-target="${targetId}"]`).classList.add('active');
            
            document.getElementById(targetId).classList.add('active');
            if(targetId === 'view-chat') this.scrollToBottom();
        },

        logout: async function() {
            try { await fetch('index.php?route=/api/auth&action=logout'); } catch(e) {}
            window.location.reload(); 
        },

        toggleTheme: function() { document.body.classList.toggle('dark-mode'); },

        initAFKTracking: function() {
            const resetTimer = () => {
                if (!this.currentUser) return;
                if (!this.isOnline) {
                    this.isOnline = true;
                    document.getElementById('my-status-dot').classList.remove('offline');
                    document.getElementById('my-status-text').innerText = "Online";
                    fetch('index.php?route=/api/auth&action=heartbeat');
                }
                clearTimeout(this.afkTimer);
                this.afkTimer = setTimeout(() => {
                    this.isOnline = false;
                    document.getElementById('my-status-dot').classList.add('offline');
                    document.getElementById('my-status-text').innerText = "Offline (AFK)";
                }, this.AFK_LIMIT);
            };
            window.addEventListener('mousemove', resetTimer);
            window.addEventListener('keypress', resetTimer);
            window.addEventListener('click', resetTimer);
            window.addEventListener('scroll', resetTimer);
            resetTimer();
        },

        startPolling: function() {
            this.pollData();
            this.pollInterval = setInterval(() => this.pollData(), 2000);
            this.heartbeatInterval = setInterval(() => { if (this.isOnline) fetch('index.php?route=/api/auth&action=heartbeat'); }, 30000);
        },

        pollData: async function() {
            if(!this.currentUser) return;
            try {
                const uRes = await fetch('index.php?route=/api/chat&action=users');
                const uData = await uRes.json();
                if (uData.status === 'success') this.renderUsers(uData.data);

                const mRes = await fetch('index.php?route=/api/chat&action=fetch');
                const mData = await mRes.json();
                if (mData.status === 'success') this.renderMessages(mData.data);
            } catch (e) { console.error("Sync Error", e); }
        },

        renderUsers: function(users) {
            const bar = document.getElementById('users-bar');
            let html = '';
            users.forEach(u => {
                if(u.role === this.currentUser) return;
                const dotClass = u.status === 'online' ? '' : 'offline';
                html += `<div class="user-pill"><div class="status-dot ${dotClass}" style="width:8px; height:8px;"></div>${u.role}</div>`;
            });
            bar.innerHTML = html;
        },

        renderMessages: function(messages) {
            const chatBox = document.getElementById('chat-messages');
            const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 50;
            chatBox.innerHTML = '<div class="sys-msg">Encryption: AES-256 Enabled. Messages Secured.</div>';
            
            messages.forEach(msg => {
                const type = msg.sender_role === this.currentUser ? 'sent' : 'recv';
                const msgDiv = document.createElement('div');
                msgDiv.className = `message msg-${type}`;
                msgDiv.innerHTML = `<span class="msg-sender">${msg.sender_role}</span><div>${msg.text}</div>`;
                chatBox.appendChild(msgDiv);
            });
            if (isAtBottom) this.scrollToBottom();
        },

        handleEnter: function(e) { if(e.key === 'Enter') this.sendMessage(); },

        sendMessage: async function() {
            const input = document.getElementById('msg-input');
            const text = input.value.trim();
            if (!text) return;
            input.value = ''; 
            
            const fd = new FormData();
            fd.append('action', 'send'); fd.append('message', text); fd.append('type', 'text');
            await fetch('index.php?route=/api/chat', { method: 'POST', body: fd });
            this.pollData(); this.scrollToBottom();
        },

        openModal: function() { document.getElementById('snippet-modal').classList.add('active'); },
        closeModal: function() { document.getElementById('snippet-modal').classList.remove('active'); },

        sendFile: async function() {
            const text = document.getElementById('snippet-text').value;
            const ext = document.getElementById('snippet-ext').value;
            if (!text) return;

            const btn = document.querySelector('.modal-actions button:last-child');
            btn.innerText = "Uploading...";

            try {
                const fd = new FormData(); fd.append('content', text); fd.append('extension', ext);
                const res = await fetch('index.php?route=/api/file', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.status === 'success') {
                    const fileHtml = `File Generated:<br><a href="${data.url}" download="${data.filename}" target="_blank" class="file-card">📄 Download ${data.filename}</a>`;
                    const msgFd = new FormData();
                    msgFd.append('action', 'send'); msgFd.append('message', fileHtml); msgFd.append('type', 'file_snippet');
                    await fetch('index.php?route=/api/chat', { method: 'POST', body: msgFd });
                    
                    this.closeModal(); document.getElementById('snippet-text').value = ''; this.pollData();
                } else { console.error(data.message); }
            } catch (e) { console.error("Upload failed", e); }
            btn.innerText = "Generate File";
        },

        scrollToBottom: function() {
            const chatBox = document.getElementById('chat-messages');
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    };

    window.onload = () => app.init();
</script>