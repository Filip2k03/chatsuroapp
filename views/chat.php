<?php require_once 'includes/header.php'; ?>

<div id="app-container">
    
    <div id="view-login" class="view active">
        <div class="login-wrapper">
            <div class="logo"><?= env('APP_NAME', 'SLOPARA') ?></div>
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

            <!-- New Security Center Section -->
            <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--glass-border); margin-top: 20px;">
                <h3 style="color: var(--text-main); margin-bottom: 1rem; font-size: 1.1rem;">Security Center</h3>
                
                <div class="admin-form-group">
                    <label>Current Passphrase</label>
                    <input type="password" id="pw-old" placeholder="Enter current password">
                </div>
                
                <div class="admin-form-group">
                    <label>New Passphrase</label>
                    <input type="password" id="pw-new" placeholder="Enter new strong password">
                </div>
                
                <div id="pw-msg" style="font-size: 0.85rem; margin-bottom: 10px; text-align: center; display: none;"></div>
                <button onclick="app.changePassword()" style="background: transparent; border: 1px solid var(--accent); color: var(--accent);">Rotate Password</button>
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

<!-- Injecting separated JS file -->
<?php require_once 'includes/scripts.php'; ?>
</body>
</html>