<?php require_once 'includes/header.php'; ?>

<div id="app-container">
    <div id="view-login" class="view active">
        <div class="login-wrapper">
            <h1 class="logo">SLOPARA</h1>
            <p style="color:var(--text-muted); margin-bottom:20px;">Enterprise Authentication</p>
            <input type="text" id="login-user" placeholder="Username">
            <input type="password" id="login-pass" placeholder="Password">
            <div id="login-error" style="color:#ff4757; font-size:0.8rem; margin-bottom:10px; display:none;"></div>
            <button onclick="app.login()">Authenticate</button>
        </div>
    </div>

    <div id="view-home" class="view">
        <div class="page-content">
            <h2 class="page-title">Directory</h2>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;">Select a user to enter the secure room.</p>
            
            <div id="directory-list">
                <!-- User Cards Populated by JS -->
            </div>
        </div>
    </div>

    <div id="view-chat" class="view">
        <div class="chat-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div id="my-status-dot" class="status-dot"></div>
                <div><strong id="my-role-display">Role</strong><div id="my-status-text" style="font-size:0.7rem; color:var(--text-muted)">Online</div></div>
            </div>
            <button onclick="app.switchTab('view-home')" class="btn-snippet" style="width:auto; padding:5px 15px; margin:0;">Back</button>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div style="text-align:center; font-size:0.8rem; color:var(--text-muted);">AES-256 Enabled</div>
        </div>
        
        <div class="chat-input">
            <button class="btn-snippet" onclick="document.getElementById('snippet-modal').classList.add('active')">📄</button>
            <input type="text" id="msg-input" placeholder="Secure message..." onkeypress="if(event.key==='Enter') app.send()">
            <button onclick="app.send()">Send</button>
        </div>
    </div>

    <div id="view-profile" class="view">
        <div class="page-content">
            <h2 class="page-title">Profile</h2>
            <div style="background:rgba(0,0,0,0.2); padding:2rem; border-radius:16px; text-align:center; border:1px solid var(--glass-border);">
                <div id="profile-avatar" style="width:80px; height:80px; background:var(--accent); border-radius:50%; margin:0 auto 1rem; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#fff; font-weight:bold;">?</div>
                <h3 id="profile-role" style="color:var(--text-main); margin-bottom:5px;">...</h3>
                <p style="color:var(--text-muted); font-size:0.9rem;">Encryption: AES-256-CBC</p>
                <div style="margin-top:20px; color:var(--online); font-size:0.85rem; font-weight:bold;">Status: Verified</div>
            </div>
        </div>
    </div>

    <div id="view-admin" class="view" style="display:none;">
        <div class="page-content">
            <h2 class="page-title">Provision User</h2>
            <input type="text" id="new-u" placeholder="New Username">
            <input type="password" id="new-p" placeholder="New Password">
            <select id="new-r"><option value="Staff">Staff</option><option value="Finance">Finance</option><option value="Admin">Admin</option></select>
            <div id="admin-msg" style="font-size:0.85rem; margin-bottom:10px;"></div>
            <button onclick="app.createUser()">Create User</button>
        </div>
    </div>

    <div id="view-settings" class="view">
        <div class="page-content">
            <h2 class="page-title">Settings</h2>
            
            <div style="background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:16px; margin-bottom:1rem; border:1px solid var(--glass-border);">
                <h3 style="font-size:1rem; margin-bottom:15px; color:var(--accent);">Rotate Password</h3>
                <input type="password" id="pw-old" placeholder="Current Password">
                <input type="password" id="pw-new" placeholder="New Password">
                <div id="pw-msg" style="font-size:0.85rem; margin-bottom:10px;"></div>
                <button onclick="app.changePw()" class="btn-snippet">Confirm Rotation</button>
            </div>
            
            <button style="background:rgba(255,71,87,0.2); color:#ff4757; border:1px solid #ff4757;" onclick="app.logout()">Terminate Session</button>
        </div>
    </div>

    <div class="bottom-nav" id="bottom-nav" style="display:none;">
        <div class="nav-item active" data-target="view-home" onclick="app.switchTab('view-home')">🏠</div>
        <div class="nav-item" data-target="view-chat" onclick="app.switchTab('view-chat')">💬</div>
        <div class="nav-item" data-target="view-profile" onclick="app.switchTab('view-profile')">👤</div>
        <div class="nav-item" id="nav-admin" style="display:none;" data-target="view-admin" onclick="app.switchTab('view-admin')">🛡️</div>
        <div class="nav-item" data-target="view-settings" onclick="app.switchTab('view-settings')">⚙️</div>
    </div>

    <div class="modal-overlay" id="snippet-modal">
        <div class="modal-content">
            <h3 style="color:var(--accent); margin-bottom:15px;">Generate File</h3>
            <textarea id="snip-txt" style="height:150px;" placeholder="Paste code or text here..."></textarea>
            <select id="snip-ext"><option value=".html">.html (Web Page)</option><option value=".py">.py (Python Script)</option></select>
            <div style="display:flex; gap:10px;">
                <button class="btn-snippet" onclick="document.getElementById('snippet-modal').classList.remove('active')">Cancel</button>
                <button onclick="app.sendFile()">Upload File</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/scripts.php'; ?>
</body>
</html>