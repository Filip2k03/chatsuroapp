<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div id="app-container">
    <div id="view-login" class="view active">
        <div class="login-wrapper">
            <h1 class="logo">SLOPARA</h1>
            <p style="color:var(--accent); font-family: monospace; letter-spacing:1px; margin-bottom:20px;">// SECURE_SYSTEM_ACCESS</p>
            <input type="text" id="login-user" placeholder="Username">
            <input type="password" id="login-pass" placeholder="Password">
            <div id="login-error" style="color:#ff4757; font-size:0.8rem; margin-bottom:10px; display:none;"></div>
            <button onclick="app.login()">AUTHENTICATE</button>
        </div>
    </div>

    <div id="view-home" class="view">
        <div class="page-content">
            <h2 class="page-title">Network Directory</h2>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;">Select a node to establish secure comms.</p>
            
            <div id="directory-list">
                <!-- User Cards Populated by JS -->
            </div>
        </div>
    </div>

    <div id="view-chat" class="view">
        <div class="chat-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div id="my-status-dot" class="status-dot"></div>
                <div>
                    <strong id="my-role-display" style="color:var(--accent)">Role</strong>
                    <div id="chat-target-label" style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">No channel selected</div>
                    <div id="my-status-text" style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Online</div>
                </div>
            </div>
            <button onclick="app.switchTab('view-home')" class="btn-snippet" style="width:auto; padding:5px 15px; margin:0;">DISCONNECT</button>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div style="text-align:center; font-size:0.8rem; color:var(--text-muted);">AES-256 Enabled</div>
        </div>
        
        <div class="chat-input">
            <button class="btn-snippet" onclick="document.getElementById('snippet-modal').classList.add('active')">{" "}</button>
            <input type="text" id="msg-input" placeholder="Execute transmission..." onkeypress="if(event.key==='Enter') app.send()">
            <button id="send-btn" onclick="app.send()" disabled>SEND</button>
        </div>
        <div id="chat-notice" class="chat-notice">Select a user before sending.</div>
    </div>

    <div id="view-profile" class="view">
        <div class="page-content">
            <h2 class="page-title">Operative Profile</h2>
            <div style="background:rgba(0,0,0,0.4); padding:2rem; border-radius:16px; text-align:center; border:1px solid var(--accent); box-shadow: inset 0 0 20px rgba(0,240,255,0.05);">
                <div id="profile-avatar" style="width:80px; height:80px; background:var(--accent); border-radius:50%; margin:0 auto 1rem; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#fff; font-weight:bold; box-shadow: 0 0 20px var(--accent);">?</div>
                <h3 id="profile-role" style="color:var(--text-main); margin-bottom:5px; text-transform:uppercase; letter-spacing:2px;">...</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; font-family:monospace;">ENC_PROTOCOL: AES-256-CBC</p>
                <div style="margin-top:20px; color:var(--online); font-size:0.85rem; font-weight:bold; letter-spacing:1px;">STATUS: VERIFIED</div>
            </div>
        </div>
    </div>

    <div id="view-admin" class="view" style="display:none;">
        <div class="page-content">
            <h2 class="page-title">System Admin | Mint Node</h2>
            <input type="text" id="new-u" placeholder="Operative Designation (Username)">
            <input type="password" id="new-p" placeholder="Security Key (Password)">
            <select id="new-r"><option value="Staff">Staff</option><option value="Finance">Finance</option><option value="Admin">Admin</option></select>
            <div id="admin-msg" style="font-size:0.85rem; margin-bottom:10px;"></div>
            <button onclick="app.createUser()">PROVISION ACCESS</button>
        </div>
    </div>

    <div id="view-settings" class="view">
        <div class="page-content">
            <h2 class="page-title">Configuration</h2>
            
            <div style="background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:16px; margin-bottom:1rem; border:1px solid var(--glass-border);">
                <h3 style="font-size:1rem; margin-bottom:15px; color:var(--accent); font-family:monospace;">[ Rotate Keys ]</h3>
                <input type="password" id="pw-old" placeholder="Current Key">
                <input type="password" id="pw-new" placeholder="New Key">
                <div id="pw-msg" style="font-size:0.85rem; margin-bottom:10px;"></div>
                <button onclick="app.changePw()" class="btn-snippet">EXECUTE ROTATION</button>
            </div>
            
            <button style="background:rgba(255,71,87,0.1); color:#ff4757; border:1px solid #ff4757; margin-top:20px;" onclick="app.logout()">TERMINATE SESSION</button>
        </div>
    </div>

    <div class="bottom-nav" id="bottom-nav" style="display:none;">
        <div class="nav-item active" data-target="view-home" onclick="app.switchTab('view-home')">🏠</div>
        <!-- Removed Chat Icon from global nav per request. Users must select via Home. -->
        <div class="nav-item" data-target="view-profile" onclick="app.switchTab('view-profile')">👤</div>
        <div class="nav-item" id="nav-admin" style="display:none;" data-target="view-admin" onclick="app.switchTab('view-admin')">🛡️</div>
        <div class="nav-item" data-target="view-settings" onclick="app.switchTab('view-settings')">⚙️</div>
    </div>

    <div class="modal-overlay" id="snippet-modal">
        <div class="modal-content" style="border: 1px solid var(--accent); box-shadow: 0 0 30px rgba(0,240,255,0.1);">
            <h3 style="color:var(--accent); margin-bottom:15px; font-family:monospace;">[ Initialize Code Snippet ]</h3>
            <textarea id="snip-txt" style="height:150px; font-family:monospace; font-size:12px; line-height:1.4;" placeholder="// Inject algorithm or payload here..."></textarea>
            
            <!-- Expanded File List for Developers -->
            <select id="snip-ext" style="font-family:monospace;">
                <option value=".js">.js (JavaScript / Node.js)</option>
                <option value=".php">.php (Backend Logic)</option>
                <option value=".css">.css (Styling / CSS3)</option>
                <option value=".html">.html (DOM Structure)</option>
                <option value=".py">.py (Python / AI Scripts)</option>
                <option value=".json">.json (Data Object)</option>
                <option value=".ts">.ts (TypeScript)</option>
                <option value=".sql">.sql (Database Query)</option>
                <option value=".md">.md (Documentation)</option>
            </select>
            
            <div style="display:flex; gap:10px;">
                <button class="btn-snippet" onclick="document.getElementById('snippet-modal').classList.remove('active')">ABORT</button>
                <button onclick="app.sendFile()">DEPLOY</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/scripts.php'; ?>
</body>
</html>
