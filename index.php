<?php
session_start();
// Pre-load session variables into PHP memory to prevent UI flashing
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slopara Secure Chat</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
            --glass-bg: rgba(255, 255, 255, 0.4);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #2d3748;
            --text-muted: #4a5568;
            --accent: #4299e1;
            --msg-sent: rgba(66, 153, 225, 0.8);
            --msg-recv: rgba(255, 255, 255, 0.6);
            --online: #48bb78;
            --offline: #a0aec0;
            --nav-bg: rgba(255, 255, 255, 0.6);
        }

        .dark-mode {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --glass-bg: rgba(15, 23, 42, 0.6);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --accent: #00f0ff;
            --msg-sent: rgba(0, 240, 255, 0.3);
            --msg-recv: rgba(30, 41, 59, 0.8);
            --nav-bg: rgba(15, 23, 42, 0.8);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }

        body {
            background: transparent;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        #app-container {
            width: 100%;
            max-width: 425px;
            height: 95vh;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .view { display: none; height: 100%; flex-direction: column; position: relative; }
        .view.active { display: flex; animation: fadeIn 0.4s ease; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }

        select, input, button {
            width: 100%; padding: 1rem; margin-bottom: 1rem;
            border-radius: 12px; border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.1); color: var(--text-main);
            font-size: 1rem; outline: none; backdrop-filter: blur(4px);
        }
        select option { background: #0f172a; color: #fff; }

        button { background: var(--accent); color: #fff; font-weight: bold; cursor: pointer; border: none; transition: all 0.3s ease; }
        button:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0, 240, 255, 0.4); }

        .login-wrapper { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 2rem; text-align: center; }
        .logo { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--accent); text-shadow: 0 0 15px rgba(0, 240, 255, 0.5); letter-spacing: 2px; }

        .chat-header { padding: 1rem; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.05); }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .status-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--online); box-shadow: 0 0 8px var(--online); transition: all 0.3s ease; }
        .status-dot.offline { background: var(--offline); box-shadow: none; }

        .users-bar { padding: 0.5rem 1rem; display: flex; gap: 1rem; overflow-x: auto; border-bottom: 1px solid var(--glass-border); font-size: 0.85rem; }
        .user-pill { display: flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 20px; white-space: nowrap; }

        .chat-messages { flex: 1; padding: 1rem; padding-bottom: 90px; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; }
        .message { max-width: 80%; padding: 0.8rem 1rem; border-radius: 16px; line-height: 1.4; animation: slideIn 0.3s ease forwards; position: relative; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .msg-sent { align-self: flex-end; background: var(--msg-sent); border-bottom-right-radius: 4px; }
        .msg-recv { align-self: flex-start; background: var(--msg-recv); border-bottom-left-radius: 4px; border: 1px solid var(--glass-border); }
        .msg-sender { font-size: 0.7rem; opacity: 0.7; margin-bottom: 4px; display: block; }

        .file-card { display: inline-flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.2); padding: 10px 15px; border-radius: 8px; text-decoration: none; color: var(--text-main); border: 1px solid var(--accent); margin-top: 5px; }
        .file-card:hover { background: rgba(0,0,0,0.4); }

        .chat-input { position: absolute; bottom: 80px; left: 0; right: 0; padding: 0.8rem 1rem; border-top: 1px solid var(--glass-border); display: flex; gap: 10px; background: var(--glass-bg); backdrop-filter: blur(10px); }
        .chat-input input { margin: 0; flex: 1; padding: 0.8rem; }
        .chat-input button { margin: 0; width: auto; padding: 0 1.2rem; }
        .btn-snippet { background: transparent !important; border: 1px solid var(--accent) !important; color: var(--accent) !important; }

        .bottom-nav { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); width: 90%; background: var(--nav-bg); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 30px; display: flex; justify-content: space-around; align-items: center; padding: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); z-index: 50; }
        .nav-item { color: var(--text-muted); cursor: pointer; padding: 10px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; width: 45px; height: 45px; }
        .nav-item:hover { color: var(--text-main); }
        .nav-item.active { color: var(--accent); background: rgba(0, 240, 255, 0.1); box-shadow: 0 0 15px rgba(0, 240, 255, 0.2); }
        .nav-item svg { width: 22px; height: 22px; fill: currentColor; }

        .page-content { flex: 1; padding: 2rem 1.5rem; overflow-y: auto; padding-bottom: 90px; }
        .page-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 1.5rem; color: var(--accent); border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem; }

        .profile-card { background: rgba(0,0,0,0.1); border: 1px solid var(--glass-border); border-radius: 16px; padding: 1.5rem; text-align: center; margin-bottom: 1.5rem; }
        .avatar { width: 80px; height: 80px; background: var(--accent); border-radius: 50%; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff; font-weight: bold; box-shadow: 0 0 20px rgba(0, 240, 255, 0.3); }
        .profile-stat { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }

        .setting-item { display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1); padding: 1rem; border-radius: 12px; margin-bottom: 1rem; border: 1px solid var(--glass-border); }
        .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--glass-border); transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--accent); }
        input:checked + .slider:before { transform: translateX(26px); }

        .modal-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: var(--glass-bg); border: 1px solid var(--glass-border); padding: 1.5rem; border-radius: 16px; width: 90%; display: flex; flex-direction: column; gap: 10px; }
        .modal-content textarea { width: 100%; height: 150px; background: rgba(0,0,0,0.2); color: var(--text-main); border: 1px solid var(--glass-border); border-radius: 8px; padding: 10px; resize: none; }
        .modal-actions { display: flex; gap: 10px; }
        .sys-msg { align-self: center; font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-bottom: 10px; }
        
        .admin-form-group { margin-bottom: 1rem; text-align: left; }
        .admin-form-group label { display: block; font-size: 0.85rem; color: var(--accent); font-weight: bold; margin-bottom: 5px; margin-left: 5px; }
    </style>
</head>
<body class="dark-mode">

    <div id="app-container">
        
        <div id="view-login" class="view active">
            <div class="login-wrapper">
                <div class="logo">SLOPARA</div>
                <p style="margin-bottom: 25px; color: var(--text-muted);">Secure Enterprise Network</p>
                
                <div class="admin-form-group">
                    <label>Username / ID</label>
                    <input type="text" id="login-user" placeholder="Enter ID">
                </div>
                
                <div class="admin-form-group">
                    <label>Passphrase</label>
                    <input type="password" id="login-pass" placeholder="Enter secure passphrase">
                    <div id="login-error" style="color: #ff4757; font-size: 0.85rem; margin-top: 8px; display: none; text-align: center;"></div>
                </div>

                <button onclick="app.login()">Authenticate</button>
            </div>
        </div>

        <div id="view-chat" class="view">
            <div class="chat-header">
                <div class="user-info">
                    <div id="my-status-dot" class="status-dot"></div>
                    <div>
                        <strong id="my-role-display">Loading...</strong>
                        <div id="my-status-text" style="font-size:0.75rem; color:var(--text-muted)">Online</div>
                    </div>
                </div>
            </div>
            
            <div class="users-bar" id="users-bar"></div>

            <div class="chat-messages" id="chat-messages">
                <div class="sys-msg">Encryption: AES-256 Enabled. Messages Secured.</div>
            </div>

            <div class="chat-input">
                <button class="btn-snippet" onclick="app.openModal()" title="Create Snippet">📄</button>
                <input type="text" id="msg-input" placeholder="Secure message..." onkeypress="app.handleEnter(event)">
                <button onclick="app.sendMessage()">Send</button>
            </div>
        </div>

        <div id="view-profile" class="view">
            <div class="page-content">
                <div class="page-title">User Profile</div>
                <div class="profile-card">
                    <div class="avatar" id="profile-avatar">?</div>
                    <h2 id="profile-name" style="margin-bottom: 5px;">...</h2>
                    <p style="color: var(--accent); font-size: 0.9rem; margin-bottom: 15px;">Enterprise Access</p>
                    <div class="profile-stat"><span style="color: var(--text-muted)">Status</span><span style="color: var(--online)">Active Now</span></div>
                    <div class="profile-stat"><span style="color: var(--text-muted)">Encryption Key</span><span style="font-family: monospace;">AES-256-CBC</span></div>
                    <div class="profile-stat" style="border: none;"><span style="color: var(--text-muted)">Role Level</span><strong id="profile-role">...</strong></div>
                </div>
                <button class="btn-snippet" style="margin-top: 10px;">Request Key Rotation</button>
            </div>
        </div>

        <div id="view-admin" class="view">
            <div class="page-content">
                <div class="page-title">Admin Dashboard</div>
                
                <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--glass-border);">
                    <h3 style="color: var(--text-main); margin-bottom: 1rem; font-size: 1.1rem;">Provision New User</h3>
                    
                    <div class="admin-form-group">
                        <label>New Username</label>
                        <input type="text" id="new-user" placeholder="e.g. jdoe">
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Initial Passphrase</label>
                        <input type="password" id="new-pass" placeholder="Enter strong password">
                    </div>

                    <div class="admin-form-group">
                        <label>Access Role</label>
                        <select id="new-role">
                            <option value="Staff">Staff (Standard)</option>
                            <option value="Finance">Finance</option>
                            <option value="Admin">Admin (Full Access)</option>
                        </select>
                    </div>

                    <div id="admin-msg" style="font-size: 0.85rem; margin-bottom: 10px; text-align: center; display: none;"></div>
                    <button onclick="app.createUser()">Create User</button>
                </div>
            </div>
        </div>

        <div id="view-settings" class="view">
            <div class="page-content">
                <div class="page-title">System Settings</div>
                <div class="setting-item">
                    <div><strong>Dark Mode</strong><div style="font-size: 0.8rem; color: var(--text-muted)">Neon Tech interface</div></div>
                    <label class="switch"><input type="checkbox" id="theme-toggle" checked onchange="app.toggleTheme()"><span class="slider"></span></label>
                </div>
                <button style="background: rgba(255, 71, 87, 0.2); color: #ff4757; border: 1px solid #ff4757; margin-top: 20px;" onclick="app.logout()">Terminate Session (Logout)</button>
            </div>
        </div>

        <div class="bottom-nav" id="bottom-nav" style="display: none;">
            <div class="nav-item active" data-target="view-chat" onclick="app.switchTab('view-chat')">
                <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
            </div>
            <div class="nav-item" data-target="view-profile" onclick="app.switchTab('view-profile')">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <!-- Admin Icon (Shield) - Hidden by default -->
            <div class="nav-item" id="nav-admin" style="display: none;" data-target="view-admin" onclick="app.switchTab('view-admin')">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
            </div>
            <div class="nav-item" data-target="view-settings" onclick="app.switchTab('view-settings')">
                <svg viewBox="0 0 24 24"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.06-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.06,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.49-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg>
            </div>
        </div>

        <div class="modal-overlay" id="snippet-modal">
            <div class="modal-content">
                <h3 style="color: var(--accent)">Create Code File</h3>
                <textarea id="snippet-text" placeholder="Paste your code or text here..."></textarea>
                <select id="snippet-ext">
                    <option value=".html">.html (Web Document)</option>
                    <option value=".py">.py (Python Script)</option>
                    <option value=".txt">.txt (Raw Text)</option>
                </select>
                <div class="modal-actions">
                    <button class="btn-snippet" onclick="app.closeModal()">Cancel</button>
                    <button onclick="app.sendFile()">Generate File</button>
                </div>
            </div>
        </div>

    </div>

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

                    const res = await fetch('auth.php', { method: 'POST', body: formData });
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
                
                // Show Admin tab if applicable
                if (role === 'Admin') {
                    document.getElementById('nav-admin').style.display = 'flex';
                }
                
                this.switchTab('view-chat'); 
                this.isOnline = true;
                this.initAFKTracking();
                this.startPolling();
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
                    const res = await fetch('auth.php', { method: 'POST', body: fd });
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
                try { await fetch('auth.php?action=logout'); } catch(e) {}
                window.location.reload(); // Hard reload clears PHP memory & JS state
            },

            toggleTheme: function() { document.body.classList.toggle('dark-mode'); },

            initAFKTracking: function() {
                const resetTimer = () => {
                    if (!this.currentUser) return;
                    if (!this.isOnline) {
                        this.isOnline = true;
                        document.getElementById('my-status-dot').classList.remove('offline');
                        document.getElementById('my-status-text').innerText = "Online";
                        fetch('auth.php?action=heartbeat');
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
                this.heartbeatInterval = setInterval(() => { if (this.isOnline) fetch('auth.php?action=heartbeat'); }, 30000);
            },

            pollData: async function() {
                if(!this.currentUser) return;
                try {
                    const uRes = await fetch('chat_api.php?action=users');
                    const uData = await uRes.json();
                    if (uData.status === 'success') this.renderUsers(uData.data);

                    const mRes = await fetch('chat_api.php?action=fetch');
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
                await fetch('chat_api.php', { method: 'POST', body: fd });
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
                    const res = await fetch('file_api.php', { method: 'POST', body: fd });
                    const data = await res.json();

                    if (data.status === 'success') {
                        const fileHtml = `File Generated:<br><a href="${data.url}" download="${data.filename}" target="_blank" class="file-card">📄 Download ${data.filename}</a>`;
                        const msgFd = new FormData();
                        msgFd.append('action', 'send'); msgFd.append('message', fileHtml); msgFd.append('type', 'file_snippet');
                        await fetch('chat_api.php', { method: 'POST', body: msgFd });
                        
                        this.closeModal(); document.getElementById('snippet-text').value = ''; this.pollData();
                    } else { alert(data.message); }
                } catch (e) { console.error("Upload failed", e); }
                btn.innerText = "Generate File";
            },

            scrollToBottom: function() {
                const chatBox = document.getElementById('chat-messages');
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        };

        // Bootstrap the app
        window.onload = () => app.init();
    </script>
</body>
</html>