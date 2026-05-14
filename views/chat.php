<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div id="app-container">
    <div id="install-banner" class="install-banner" hidden>
        <div>
            <strong>Install Slopara</strong>
            <span id="install-copy">Add this secure chat to your home screen.</span>
        </div>
        <button id="install-btn" type="button">Install</button>
        <button id="install-dismiss-btn" type="button" class="ghost-btn">Later</button>
    </div>

    <div id="view-login" class="view <?= $isLoggedIn ? '' : 'active' ?>">
        <div class="login-wrapper">
            <h1 class="logo">SLOPARA</h1>
            <p class="login-subtitle">// SECURE_SYSTEM_ACCESS_V3</p>
            <input type="text" id="login-user" placeholder="Username">
            <input type="password" id="login-pass" placeholder="Password">
            <div id="login-error" style="color:#ff4757; font-size:0.8rem; margin-bottom:10px; display:none;"></div>
            <button id="login-btn">AUTHENTICATE</button>
        </div>
    </div>

    <div id="view-home" class="view <?= $isLoggedIn ? 'active' : '' ?>">
        <div class="page-content">
            <div class="section-head">
                <div>
                    <h2 class="page-title">Network Directory</h2>
                    <p class="section-copy">Select a user to open a private encrypted channel.</p>
                </div>
                <div class="mini-stat"><span id="directory-count">0</span><small>nodes</small></div>
            </div>
            <input type="search" id="directory-search" class="compact-input" placeholder="Search users...">
            
            <div id="directory-list">
                <!-- User Cards Populated by JS -->
            </div>
        </div>
    </div>

    <div id="view-chat" class="view">
        <div class="chat-header">
            <div class="chat-peer">
                <div id="my-status-dot" class="status-dot"></div>
                <div>
                    <strong id="my-role-display" style="color:var(--accent)">Role</strong>
                    <div id="chat-target-label" style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">No channel selected</div>
                    <div id="my-status-text" style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Online</div>
                </div>
            </div>
            <button id="chat-back-btn" class="btn-snippet compact-btn">BACK</button>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div style="text-align:center; font-size:0.8rem; color:var(--text-muted);">AES-256 Enabled</div>
        </div>
        
        <div class="chat-input">
            <button id="snippet-open-btn" class="btn-snippet tool-btn" title="Attach code snippet">+</button>
            <input type="text" id="msg-input" placeholder="Write a secure message...">
            <button id="send-btn" disabled>SEND</button>
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

    <div id="view-admin" class="view">
        <div class="page-content">
            <h2 class="page-title">System Admin | Mint Node</h2>
            <input type="text" id="new-u" placeholder="Operative Designation (Username)">
            <input type="password" id="new-p" placeholder="Security Key (Password)">
            <select id="new-r"><option value="Staff">Staff</option><option value="Finance">Finance</option><option value="Admin">Admin</option></select>
            <div id="admin-msg" style="font-size:0.85rem; margin-bottom:10px;"></div>
            <button id="create-user-btn">PROVISION ACCESS</button>

            <div class="admin-divider"></div>
            <h3 class="panel-title">Update Role</h3>
            <input type="text" id="role-u" placeholder="Existing Username">
            <select id="role-r"><option value="Staff">Staff</option><option value="Finance">Finance</option><option value="Admin">Admin</option></select>
            <div id="role-msg" style="font-size:0.85rem; margin-bottom:10px;"></div>
            <button id="update-role-btn" class="btn-snippet">UPDATE ROLE</button>
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
                <button id="change-password-btn" class="btn-snippet">EXECUTE ROTATION</button>
            </div>
            <div class="settings-panel">
                <h3 class="panel-title">App Install</h3>
                <p id="install-status" class="section-copy">Checking install availability...</p>
                <button id="settings-install-btn" class="btn-snippet" type="button">INSTALL APP</button>
            </div>
            
            <button id="logout-btn" class="danger-btn">TERMINATE SESSION</button>
        </div>
    </div>

    <div class="bottom-nav" id="bottom-nav" style="<?= $isLoggedIn ? 'display:flex;' : 'display:none;' ?>">
        <button class="nav-item active" data-target="view-home" type="button">Home</button>
        <!-- Removed Chat Icon from global nav per request. Users must select via Home. -->
        <button class="nav-item" data-target="view-profile" type="button">Profile</button>
        <button class="nav-item" id="nav-admin" style="<?= strtolower($userRole) === 'admin' ? 'display:block;' : 'display:none;' ?>" data-target="view-admin" type="button">Admin</button>
        <button class="nav-item" data-target="view-settings" type="button">Settings</button>
    </div>

    <div class="modal-overlay" id="snippet-modal">
        <div class="modal-content" style="border: 1px solid var(--accent); box-shadow: 0 0 30px rgba(0,240,255,0.1);">
            <h3 style="color:var(--accent); margin-bottom:15px; font-family:monospace;">[ Initialize Code Snippet ]</h3>
            <textarea id="snip-txt" style="height:150px; font-family:monospace; font-size:12px; line-height:1.4;" placeholder="// Inject algorithm or payload here..."></textarea>
            
            <select id="snip-ext" style="font-family:monospace;">
                <option value=".txt">.txt (Plain Text)</option>
                <option value=".md">.md (Markdown)</option>
                <option value=".json">.json (Data Object)</option>
                <option value=".py">.py (Python / AI Scripts)</option>
                <option value=".js">.js (JavaScript / Node.js)</option>
                <option value=".ts">.ts (TypeScript)</option>
                <option value=".jsx">.jsx (React JSX)</option>
                <option value=".tsx">.tsx (React TSX)</option>
                <option value=".php">.php (Backend Logic)</option>
                <option value=".css">.css (Styling / CSS3)</option>
                <option value=".html">.html (DOM Structure)</option>
                <option value=".sql">.sql (Database Query)</option>
                <option value=".sh">.sh (Shell Script)</option>
                <option value=".yaml">.yaml (Config)</option>
                <option value=".csv">.csv (Table Data)</option>
            </select>
            
            <div style="display:flex; gap:10px;">
                <button id="snippet-close-btn" class="btn-snippet">ABORT</button>
                <button id="snippet-send-btn">DEPLOY</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="ios-install-modal">
        <div class="modal-content">
            <h3 class="panel-title">Install on iPhone or iPad</h3>
            <p class="section-copy">Open Safari, tap Share, then choose Add to Home Screen. iOS requires this manual step for web apps.</p>
            <div class="ios-steps">
                <div><strong>1</strong><span>Tap Share</span></div>
                <div><strong>2</strong><span>Add to Home Screen</span></div>
                <div><strong>3</strong><span>Open Slopara from your Home Screen</span></div>
            </div>
            <button id="ios-install-close-btn" class="btn-snippet" type="button">GOT IT</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/scripts.php'; ?>
</body>
</html>
