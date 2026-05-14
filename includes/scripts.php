<script>
const api = {
    async request(route, payload = {}, options = {}) {
        const method = options.method || 'POST';
        const query = method === 'GET' ? `&${new URLSearchParams(payload).toString()}` : '';

        const response = await fetch(`index.php?route=${route}${query}`, {
            method,
            headers: method === 'GET' ? {} : {'Content-Type': 'application/json'},
            body: method === 'GET' ? undefined : JSON.stringify(payload),
            credentials: 'same-origin'
        });

        let data = null;
        try {
            data = await response.json();
        } catch (e) {
            data = {status: 'error', message: 'Server returned an invalid response.', data: []};
        }

        if (!response.ok || (data.status !== 'success' && data.status !== 'alive' && data.status !== 'logged_out')) {
            const error = new Error(data.message || 'Request failed.');
            error.response = response;
            error.data = data;
            throw error;
        }

        return data;
    },

    auth(action, payload = {}) {
        return this.request('/api/auth', {action, ...payload});
    },

    chat(action, payload = {}, method = 'POST') {
        return this.request('/api/chat', {action, ...payload}, {method});
    },

    uploadSnippet(payload = {}) {
        const fd = new FormData();
        Object.entries(payload).forEach(([key, value]) => fd.append(key, value));

        return fetch('index.php?route=/api/file', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        }).then(async response => {
            const data = await response.json();
            if (!response.ok || data.status !== 'success') {
                throw new Error(data.message || 'Snippet upload failed.');
            }
            return data;
        });
    }
};

const app = {
    role: null,
    online: true,
    afkTimer: null,
    pollTimer: null,
    hbTimer: null,
    currentReceiverId: null,
    currentReceiverName: '',
    lastMsgId: 0,
    currentPollInterval: 2000,
    minPollInterval: 2000,
    maxPollInterval: 15000,

    init() {
        const isLogged = <?= $isLoggedIn ? 'true' : 'false' ?>;
        if (isLogged) this.completeLogin(<?= json_encode($userRole) ?>);
        this.updateComposerState();
    },

    async login() {
        const username = document.getElementById('login-user').value.trim();
        const password = document.getElementById('login-pass').value;
        const err = document.getElementById('login-error');

        if (!username || !password) {
            err.innerText = 'Username and password are required.';
            err.style.display = 'block';
            return;
        }

        try {
            const data = await api.auth('login', {username, password});
            err.style.display = 'none';
            this.completeLogin(data.role || data.data.role);
        } catch (e) {
            err.innerText = e.message;
            err.style.display = 'block';
        }
    },

    completeLogin(role) {
        this.role = role || '';
        document.getElementById('my-role-display').innerText = this.role;
        document.getElementById('profile-role').innerText = this.role;
        document.getElementById('profile-avatar').innerText = this.role.charAt(0) || '?';
        document.getElementById('view-login').classList.remove('active');
        document.getElementById('bottom-nav').style.display = 'flex';

        if (this.role.toLowerCase() === 'admin') {
            document.getElementById('nav-admin').style.display = 'flex';
        }

        this.switchTab('view-home');
        this.initAFK();
        this.pollData();
        this.hbTimer = setInterval(() => {
            if (this.online) api.auth('heartbeat').catch(() => {});
        }, 30000);
    },

    switchTab(id) {
        document.querySelectorAll('.view').forEach(v => {
            if (v.id !== 'view-login') v.classList.remove('active');
        });
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active');

        const navTarget = document.querySelector(`.nav-item[data-target="${id}"]`);
        if (navTarget) navTarget.classList.add('active');
        if (id === 'view-chat') this.scroll();
    },

    selectUser(id, name, role) {
        this.currentReceiverId = parseInt(id, 10);
        this.currentReceiverName = name || role || 'Selected user';
        this.lastMsgId = 0;
        this.currentPollInterval = this.minPollInterval;
        document.getElementById('chat-target-label').innerText = `Channel: ${this.currentReceiverName}`;
        document.getElementById('chat-messages').innerHTML = '<div style="text-align:center; font-size:0.8rem; color:var(--accent); margin-bottom:10px; text-transform:uppercase; letter-spacing: 2px;">Secured Channel</div>';
        this.updateComposerState();
        this.showChatNotice(`Ready to message ${this.currentReceiverName}.`, 'success');
        this.switchTab('view-chat');
        clearTimeout(this.pollTimer);
        this.pollData();
    },

    updateComposerState() {
        const hasReceiver = Boolean(this.currentReceiverId);
        const sendBtn = document.getElementById('send-btn');
        if (sendBtn) sendBtn.disabled = !hasReceiver;
    },

    showChatNotice(message, type = '') {
        const notice = document.getElementById('chat-notice');
        if (!notice) return;
        notice.innerText = message;
        notice.className = `chat-notice active ${type}`.trim();
        clearTimeout(this.noticeTimer);
        this.noticeTimer = setTimeout(() => notice.classList.remove('active'), 3500);
    },

    escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    },

    async pollData() {
        if (!this.role) return;

        try {
            const uData = await api.chat('users', {}, 'GET');
            const users = Array.isArray(uData.data) ? uData.data : [];
            let html = '';

            users.forEach(u => {
                const isOnline = u.status === 'online';
                const userName = this.escapeHtml(u.username || u.role);
                const userRole = this.escapeHtml(u.role);
                const userNameArg = this.escapeHtml(JSON.stringify(u.username || u.role));
                const userRoleArg = this.escapeHtml(JSON.stringify(u.role));

                html += `
                    <div class="directory-card" onclick="app.selectUser(${parseInt(u.id, 10)}, ${userNameArg}, ${userRoleArg})">
                        <div class="dir-info">
                            <div class="dir-avatar">${userRole.charAt(0)}</div>
                            <div>
                                <strong style="color:var(--accent); letter-spacing:1px;">${userName}</strong>
                                <div style="font-size:0.75rem; color:var(--text-muted)">${userRole}</div>
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
        } catch (e) {
            document.getElementById('directory-list').innerHTML = `<div style="text-align:center; color:#ff8a95; margin-top:2rem;">${this.escapeHtml(e.message)}</div>`;
        }

        if (!this.currentReceiverId) {
            this.pollTimer = setTimeout(() => this.pollData(), this.currentPollInterval);
            return;
        }

        try {
            const mData = await api.chat('fetch', {
                last_id: this.lastMsgId,
                receiver_id: this.currentReceiverId
            }, 'GET');
            const messages = Array.isArray(mData.data) ? mData.data : (mData.data.messages || []);

            if (messages.length > 0) {
                const cb = document.getElementById('chat-messages');
                const bottom = cb.scrollHeight - cb.scrollTop <= cb.clientHeight + 50;

                if (this.lastMsgId === 0) {
                    cb.innerHTML = '<div style="text-align:center; font-size:0.8rem; color:var(--accent); margin-bottom:10px; text-transform:uppercase; letter-spacing: 2px;">Secured Channel</div>';
                }

                cb.innerHTML += messages.map(m => {
                    const text = m.message_type === 'file_snippet' ? m.text : this.escapeHtml(m.text);
                    return `<div class="message msg-${m.sender_role===this.role?'sent':'recv'}"><div style="font-size:0.7rem;opacity:0.7;margin-bottom:3px;">${this.escapeHtml(m.sender_name || m.sender_role)}</div>${text}</div>`;
                }).join('');

                this.lastMsgId = Math.max(...messages.map(m => parseInt(m.id, 10)));
                this.currentPollInterval = this.minPollInterval;
                if (bottom) this.scroll();
            } else {
                this.currentPollInterval = Math.min(this.maxPollInterval, this.currentPollInterval + 2000);
            }
        } catch (e) {
            this.showChatNotice(e.message, 'error');
        }

        this.pollTimer = setTimeout(() => this.pollData(), this.currentPollInterval);
    },

    async send() {
        const input = document.getElementById('msg-input');
        const message = input.value.trim();

        if (!this.currentReceiverId) {
            this.showChatNotice('Select a user before sending.', 'error');
            return;
        }

        if (!message) {
            this.showChatNotice('Enter a message before sending.', 'error');
            return;
        }

        input.value = '';
        this.currentPollInterval = this.minPollInterval;

        try {
            await api.chat('send', {
                message,
                type: 'text',
                receiver_id: this.currentReceiverId
            });
            clearTimeout(this.pollTimer);
            this.pollData();
            this.scroll();
        } catch (e) {
            input.value = message;
            this.showChatNotice(e.message, 'error');
        }
    },

    async sendFile() {
        if (!this.currentReceiverId) {
            this.showChatNotice('Select a user before sending a snippet.', 'error');
            return;
        }

        const txt = document.getElementById('snip-txt').value;
        const ext = document.getElementById('snip-ext').value;
        if (!txt) return;

        try {
            const data = await api.uploadSnippet({content: txt, extension: ext});
            await api.chat('send', {
                type: 'file_snippet',
                receiver_id: this.currentReceiverId,
                message: `System Code Artifact Generated:<br><a href="${data.url}" download class="file-card">Extract ${this.escapeHtml(data.filename)}</a>`
            });

            this.currentPollInterval = this.minPollInterval;
            document.getElementById('snippet-modal').classList.remove('active');
            document.getElementById('snip-txt').value = '';
            clearTimeout(this.pollTimer);
            this.pollData();
        } catch (e) {
            this.showChatNotice(e.message, 'error');
        }
    },

    async createUser() {
        const msg = document.getElementById('admin-msg');
        msg.innerText = 'Creating user...';
        msg.style.color = 'var(--text-muted)';

        try {
            const data = await api.auth('create_user', {
                username: document.getElementById('new-u').value.trim(),
                password: document.getElementById('new-p').value,
                role: document.getElementById('new-r').value
            });

            msg.innerText = data.message || 'User created.';
            msg.style.color = 'var(--online)';
            document.getElementById('new-u').value = '';
            document.getElementById('new-p').value = '';
            this.currentPollInterval = this.minPollInterval;
            clearTimeout(this.pollTimer);
            this.pollData();
        } catch (e) {
            msg.innerText = e.message;
            msg.style.color = '#ff4757';
        }
    },

    async changePw() {
        const msg = document.getElementById('pw-msg');
        try {
            const data = await api.auth('change_password', {
                old_password: document.getElementById('pw-old').value,
                new_password: document.getElementById('pw-new').value
            });
            msg.innerText = data.message;
            msg.style.color = 'var(--online)';
        } catch (e) {
            msg.innerText = e.message;
            msg.style.color = '#ff4757';
        }
    },

    initAFK() {
        const rst = () => {
            if (!this.online) {
                this.online = true;
                this.currentPollInterval = this.minPollInterval;
                document.getElementById('my-status-dot').className = 'status-dot';
                document.getElementById('my-status-text').innerText = 'Online';
                api.auth('heartbeat').catch(() => {});
            }
            clearTimeout(this.afkTimer);
            this.afkTimer = setTimeout(() => {
                this.online = false;
                this.currentPollInterval = this.maxPollInterval;
                document.getElementById('my-status-dot').className = 'status-dot offline';
                document.getElementById('my-status-text').innerText = 'AFK';
            }, 120000);
        };

        ['mousemove','keypress','click','scroll','touchstart'].forEach(e => window.addEventListener(e, rst, {passive: true}));
        rst();
    },

    async logout() {
        await api.auth('logout').catch(() => {});
        window.location.reload();
    },

    scroll() {
        const b = document.getElementById('chat-messages');
        b.scrollTop = b.scrollHeight;
    }
};

window.onload = () => app.init();
</script>
