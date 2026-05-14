(function () {
    'use strict';

    const config = window.SLOPARA_CONFIG || {isLoggedIn: false, userRole: '', apiBase: 'index.php'};

    const $ = (id) => document.getElementById(id);

    const api = {
        async request(route, payload = {}, options = {}) {
            const method = options.method || 'POST';
            const query = method === 'GET' ? `&${new URLSearchParams(payload).toString()}` : '';
            const response = await fetch(`${config.apiBase}?route=${route}${query}`, {
                method,
                headers: method === 'GET' ? {} : {'Content-Type': 'application/json'},
                body: method === 'GET' ? undefined : JSON.stringify(payload),
                credentials: 'same-origin',
                cache: 'no-store'
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                data = {status: 'error', message: 'Server returned an invalid response.', data: []};
            }

            if (!response.ok || !['success', 'alive', 'logged_out'].includes(data.status)) {
                const error = new Error(data.message || 'Request failed.');
                error.data = data;
                error.response = response;
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

        async uploadSnippet(payload = {}) {
            const fd = new FormData();
            Object.entries(payload).forEach(([key, value]) => fd.append(key, value));
            const response = await fetch(`${config.apiBase}?route=/api/file`, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await response.json();
            if (!response.ok || data.status !== 'success') {
                throw new Error(data.message || 'Snippet upload failed.');
            }
            return data.data || data;
        }
    };

    const app = {
        role: null,
        users: [],
        online: true,
        busySending: false,
        afkTimer: null,
        pollTimer: null,
        hbTimer: null,
        noticeTimer: null,
        currentReceiverId: null,
        currentReceiverName: '',
        deferredInstallPrompt: null,
        installDismissed: localStorage.getItem('slopara_install_dismissed') === '1',
        notificationPermissionDismissed: localStorage.getItem('slopara_notify_dismissed') === '1',
        lastNotifiedMsgId: 0,
        lastMsgId: 0,
        currentPollInterval: 2000,
        minPollInterval: 2000,
        maxPollInterval: 15000,

        init() {
            this.bindEvents();
            this.initInstallFlow();
            this.initNotificationFlow();
            if (config.isLoggedIn) this.completeLogin(config.userRole);
            this.updateComposerState();
        },

        bindEvents() {
            $('login-btn')?.addEventListener('click', () => this.login());
            $('login-pass')?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') this.login();
            });
            $('directory-search')?.addEventListener('input', () => this.renderDirectory());
            $('chat-back-btn')?.addEventListener('click', () => this.switchTab('view-home'));
            $('snippet-open-btn')?.addEventListener('click', () => this.openSnippetModal());
            $('snippet-close-btn')?.addEventListener('click', () => this.closeSnippetModal());
            $('snippet-send-btn')?.addEventListener('click', () => this.sendFile());
            $('send-btn')?.addEventListener('click', () => this.send());
            $('msg-input')?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) this.send();
            });
            $('create-user-btn')?.addEventListener('click', () => this.createUser());
            $('update-role-btn')?.addEventListener('click', () => this.updateUserRole());
            $('change-password-btn')?.addEventListener('click', () => this.changePw());
            $('logout-btn')?.addEventListener('click', () => this.logout());
            $('install-btn')?.addEventListener('click', () => this.installApp());
            $('settings-install-btn')?.addEventListener('click', () => this.installApp());
            $('notify-btn')?.addEventListener('click', () => this.openNotificationPrompt());
            $('notify-modal-enable-btn')?.addEventListener('click', () => this.requestNotificationPermission());
            $('notify-modal-close-btn')?.addEventListener('click', () => this.closeNotificationModal());
            $('install-dismiss-btn')?.addEventListener('click', () => this.dismissInstall());
            $('ios-install-close-btn')?.addEventListener('click', () => $('ios-install-modal')?.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', () => this.switchTab(item.dataset.target));
            });
        },

        initInstallFlow() {
            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                this.deferredInstallPrompt = event;
                this.updateInstallUi();
            });

            window.addEventListener('appinstalled', () => {
                this.deferredInstallPrompt = null;
                localStorage.setItem('slopara_install_dismissed', '1');
                this.updateInstallUi();
            });

            this.updateInstallUi();
        },

        initNotificationFlow() {
            this.updateNotificationUi();
            if (Notification.permission === 'default' && !this.notificationPermissionDismissed) {
                this.showNotificationToast();
            }
            if (Notification.permission === 'denied') {
                this.showNotificationToast('Notifications are blocked. Enable them in browser settings.', 'error');
            }
        },

        isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        },

        isIos() {
            return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        },

        updateInstallUi() {
            const installed = this.isStandalone();
            const banner = $('install-banner');
            const status = $('install-status');
            const settingsBtn = $('settings-install-btn');
            const available = installed || this.deferredInstallPrompt || this.isIos();

            if (status) {
                status.innerText = installed
                    ? 'Slopara is already running as an installed app.'
                    : this.isIos()
                        ? 'Install from Safari using Share, then Add to Home Screen.'
                        : this.deferredInstallPrompt
                            ? 'Install Slopara for faster access and a standalone app window.'
                            : 'Install will appear when your browser marks the app as installable.';
            }

            if (settingsBtn) {
                settingsBtn.disabled = installed || !available;
                settingsBtn.innerText = installed ? 'INSTALLED' : this.isIos() ? 'SHOW IOS STEPS' : 'INSTALL APP';
            }

            if (banner && !installed && !this.installDismissed && (this.deferredInstallPrompt || this.isIos())) {
                $('install-copy').innerText = this.isIos()
                    ? 'Add to your iPhone Home Screen from Safari.'
                    : 'Install for a faster app-like experience.';
                banner.hidden = false;
            }
        },

        updateNotificationUi() {
            const status = $('notify-status');
            const btn = $('notify-btn');
            if (!status || !btn) return;

            if (!('Notification' in window)) {
                status.innerText = 'This browser does not support notifications.';
                btn.disabled = true;
                btn.innerText = 'UNSUPPORTED';
                return;
            }

            if (Notification.permission === 'granted') {
                status.innerText = 'Notifications are enabled for message alerts.';
                btn.disabled = true;
                btn.innerText = 'ENABLED';
                this.hideNotificationToast();
                return;
            }

            if (Notification.permission === 'denied') {
                status.innerText = 'Notifications are blocked. Open browser settings to allow them.';
                btn.disabled = false;
                btn.innerText = 'OPEN HELP';
                this.showNotificationToast('Notifications are blocked in this browser.', 'error');
                return;
            }

            status.innerText = 'Notifications are off. Tap below to allow alerts.';
            btn.disabled = false;
            btn.innerText = 'ENABLE NOTIFICATIONS';
        },

        showNotificationToast(message, type = '') {
            let toast = $('notification-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'notification-toast';
                toast.className = 'notification-toast';
                toast.innerHTML = `
                    <div class="toast-copy">
                        <strong id="notification-toast-title">Notifications</strong>
                        <span id="notification-toast-body"></span>
                    </div>
                    <button id="notification-toast-action" type="button">Enable</button>
                    <button id="notification-toast-close" type="button" class="ghost-btn">Later</button>
                `;
                $('app-container')?.appendChild(toast);
                $('notification-toast-action')?.addEventListener('click', () => this.openNotificationPrompt());
                $('notification-toast-close')?.addEventListener('click', () => this.dismissNotificationToast());
            }

            $('notification-toast-title').innerText = type === 'error' ? 'Notifications blocked' : 'Enable alerts';
            $('notification-toast-body').innerText = message;
            toast.className = `notification-toast active ${type}`.trim();
        },

        hideNotificationToast() {
            $('notification-toast')?.classList.remove('active');
        },

        dismissNotificationToast() {
            this.notificationPermissionDismissed = true;
            localStorage.setItem('slopara_notify_dismissed', '1');
            this.hideNotificationToast();
        },

        openNotificationModal() {
            $('notify-modal')?.classList.add('active');
            $('notify-modal-status').innerText = Notification.permission === 'denied'
                ? 'Browser notifications are blocked. You must allow them in browser settings.'
                : 'Slopara will ask the browser for permission when you tap Enable.';
        },

        closeNotificationModal() {
            $('notify-modal')?.classList.remove('active');
        },

        async requestNotificationPermission() {
            if (!('Notification' in window)) {
                this.showChatNotice('This browser does not support notifications.', 'error');
                return;
            }

            if (Notification.permission === 'granted') {
                this.updateNotificationUi();
                this.closeNotificationModal();
                return;
            }

            if (Notification.permission === 'denied') {
                this.showChatNotice('Notifications are blocked in browser settings.', 'error');
                this.updateNotificationUi();
                return;
            }

            try {
                const result = await Notification.requestPermission();
                this.closeNotificationModal();
                this.updateNotificationUi();
                if (result === 'granted') {
                    this.showChatNotice('Notifications enabled.', 'success');
                    this.notify('Slopara', 'Message alerts are now enabled.');
                } else {
                    this.showChatNotice('Notification permission was not granted.', 'error');
                    this.showNotificationToast('Permission was not granted. You can enable it later from the app settings.', 'error');
                }
            } catch (e) {
                this.showChatNotice('Unable to request notification permission.', 'error');
            }
        },

        notify(title, body) {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;
            try {
                const n = new Notification(title, {
                    body,
                    icon: 'https://placehold.co/192x192/0f172a/00f0ff?text=S',
                    badge: 'https://placehold.co/96x96/0f172a/00f0ff?text=S',
                    tag: 'slopara-message',
                    renotify: true
                });
                n.onclick = () => window.focus();
            } catch (e) {}
        },

        async installApp() {
            if (this.isStandalone()) return;

            if (this.isIos()) {
                $('ios-install-modal')?.classList.add('active');
                return;
            }

            if (!this.deferredInstallPrompt) {
                this.showChatNotice('Install is not available in this browser yet.', 'error');
                return;
            }

            this.deferredInstallPrompt.prompt();
            await this.deferredInstallPrompt.userChoice.catch(() => null);
            this.deferredInstallPrompt = null;
            this.updateInstallUi();
        },

        dismissInstall() {
            this.installDismissed = true;
            localStorage.setItem('slopara_install_dismissed', '1');
            if ($('install-banner')) $('install-banner').hidden = true;
        },

        async login() {
            const username = $('login-user').value.trim();
            const password = $('login-pass').value;
            const err = $('login-error');
            if (!username || !password) {
                err.innerText = 'Username and password are required.';
                err.style.display = 'block';
                return;
            }

            try {
                $('login-btn').disabled = true;
                const data = await api.auth('login', {username, password});
                err.style.display = 'none';
                this.completeLogin(data.role || data.data.role);
            } catch (e) {
                err.innerText = e.message;
                err.style.display = 'block';
            } finally {
                $('login-btn').disabled = false;
            }
        },

        completeLogin(role) {
            this.role = role || '';
            $('my-role-display').innerText = this.role;
            $('profile-role').innerText = this.role;
            $('profile-avatar').innerText = this.role.charAt(0) || '?';
            $('view-login').classList.remove('active');
            $('view-admin')?.classList.remove('active');
            $('bottom-nav').style.display = 'flex';
            if (this.role.toLowerCase() === 'admin') $('nav-admin').style.display = 'block';
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
            $(id)?.classList.add('active');
            document.querySelector(`.nav-item[data-target="${id}"]`)?.classList.add('active');
            if (id === 'view-chat') this.scroll();
        },

        selectUser(id, name, role) {
            this.currentReceiverId = parseInt(id, 10);
            this.currentReceiverName = name || role || 'Selected user';
            this.lastMsgId = 0;
            this.currentPollInterval = this.minPollInterval;
            $('chat-target-label').innerText = `Channel: ${this.currentReceiverName}`;
            $('chat-messages').innerHTML = '<div style="text-align:center; font-size:0.8rem; color:var(--accent); margin-bottom:10px; text-transform:uppercase; letter-spacing: 2px;">Secured Channel</div>';
            this.updateComposerState();
            this.showChatNotice(`Ready to message ${this.currentReceiverName}.`, 'success');
            this.switchTab('view-chat');
            clearTimeout(this.pollTimer);
            this.pollData();
        },

        renderDirectory() {
            const query = ($('directory-search')?.value || '').trim().toLowerCase();
            const users = this.users.filter(user => {
                const haystack = `${user.username || ''} ${user.role || ''}`.toLowerCase();
                return haystack.includes(query);
            });

            $('directory-count').innerText = String(this.users.length);
            $('directory-list').innerHTML = users.map(user => this.directoryCard(user)).join('') ||
                '<div style="text-align:center; color:var(--text-muted); margin-top:2rem;">No users available.</div>';

            document.querySelectorAll('[data-user-id]').forEach(card => {
                card.addEventListener('click', () => {
                    this.selectUser(card.dataset.userId, card.dataset.username, card.dataset.role);
                });
            });
        },

        directoryCard(user) {
            const isOnline = user.status === 'online';
            const name = this.escapeHtml(user.username || user.role);
            const role = this.escapeHtml(user.role);
            return `
                <div class="directory-card" data-user-id="${parseInt(user.id, 10)}" data-username="${name}" data-role="${role}">
                    <div class="dir-info">
                        <div class="dir-avatar">${role.charAt(0)}</div>
                        <div>
                            <strong style="color:var(--accent-strong);">${name}</strong>
                            <div style="font-size:0.75rem; color:var(--text-muted)">${role}</div>
                            <div style="font-size:0.8rem; color:var(--text-muted)">
                                <span class="status-dot ${isOnline ? '' : 'offline'}" style="display:inline-block; margin-right:5px;"></span>
                                ${isOnline ? 'Active now' : 'Offline'}
                            </div>
                        </div>
                    </div>
                    <span style="font-size:1.1rem; color:var(--accent);">Open</span>
                </div>
            `;
        },

        updateComposerState() {
            const ready = Boolean(this.currentReceiverId) && !this.busySending;
            if ($('send-btn')) $('send-btn').disabled = !ready;
            if ($('snippet-open-btn')) $('snippet-open-btn').disabled = !this.currentReceiverId;
        },

        showChatNotice(message, type = '') {
            const notice = $('chat-notice');
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
                this.users = Array.isArray(uData.data) ? uData.data : [];
                this.renderDirectory();
            } catch (e) {
                $('directory-list').innerHTML = `<div style="text-align:center; color:#ff9aa7; margin-top:2rem;">${this.escapeHtml(e.message)}</div>`;
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
                this.renderMessages(messages);
            } catch (e) {
                this.showChatNotice(e.message, 'error');
            }

            this.pollTimer = setTimeout(() => this.pollData(), this.currentPollInterval);
        },

        renderMessages(messages) {
            if (!messages.length) {
                this.currentPollInterval = Math.min(this.maxPollInterval, this.currentPollInterval + 2000);
                return;
            }

            const box = $('chat-messages');
            const bottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 50;
            if (this.lastMsgId === 0) {
                box.innerHTML = '<div style="text-align:center; font-size:0.8rem; color:var(--accent); margin-bottom:10px; text-transform:uppercase; letter-spacing: 2px;">Secured Channel</div>';
            }

            box.innerHTML += messages.map(message => {
                const text = message.message_type === 'file_snippet' ? message.text : this.escapeHtml(message.text);
                return `<div class="message msg-${message.sender_role === this.role ? 'sent' : 'recv'}"><div style="font-size:0.7rem;opacity:0.7;margin-bottom:3px;">${this.escapeHtml(message.sender_name || message.sender_role)}</div>${text}</div>`;
            }).join('');

            this.lastMsgId = Math.max(...messages.map(m => parseInt(m.id, 10)));
            this.currentPollInterval = this.minPollInterval;
            if (bottom) this.scroll();

            const latestIncoming = messages.filter(m => m.sender_role !== this.role).slice(-1)[0];
            if (latestIncoming && latestIncoming.id > this.lastNotifiedMsgId) {
                this.lastNotifiedMsgId = latestIncoming.id;
                if (document.visibilityState !== 'visible' || this.currentReceiverId !== parseInt(this.currentReceiverId, 10)) {
                    this.notify(`New message from ${latestIncoming.sender_name || latestIncoming.sender_role}`, latestIncoming.text || 'Open Slopara to read it.');
                }
            }
        },

        async send() {
            const input = $('msg-input');
            const message = input.value.trim();
            if (!this.currentReceiverId) return this.showChatNotice('Select a user before sending.', 'error');
            if (!message) return this.showChatNotice('Enter a message before sending.', 'error');

            this.busySending = true;
            this.updateComposerState();
            input.value = '';
            this.currentPollInterval = this.minPollInterval;

            try {
                await api.chat('send', {message, type: 'text', receiver_id: this.currentReceiverId});
                clearTimeout(this.pollTimer);
                await this.pollData();
                this.scroll();
            } catch (e) {
                input.value = message;
                this.showChatNotice(e.message, 'error');
            } finally {
                this.busySending = false;
                this.updateComposerState();
            }
        },

        openSnippetModal() {
            if (!this.currentReceiverId) return this.showChatNotice('Select a user before attaching a snippet.', 'error');
            $('snippet-modal').classList.add('active');
            $('snip-txt').focus();
        },

        closeSnippetModal() {
            $('snippet-modal').classList.remove('active');
        },

        async sendFile() {
            if (!this.currentReceiverId) return this.showChatNotice('Select a user before sending a snippet.', 'error');
            const txt = $('snip-txt').value;
            const ext = $('snip-ext').value;
            if (!txt.trim()) return this.showChatNotice('Snippet content is required.', 'error');

            try {
                $('snippet-send-btn').disabled = true;
                const file = await api.uploadSnippet({content: txt, extension: ext});
                await api.chat('send', {
                    type: 'file_snippet',
                    receiver_id: this.currentReceiverId,
                    message: `Code artifact generated:<br><a href="${this.escapeHtml(file.url)}" download class="file-card">Download ${this.escapeHtml(file.filename)}</a>`
                });
                this.closeSnippetModal();
                $('snip-txt').value = '';
                clearTimeout(this.pollTimer);
                await this.pollData();
            } catch (e) {
                this.showChatNotice(e.message, 'error');
            } finally {
                $('snippet-send-btn').disabled = false;
            }
        },

        async createUser() {
            await this.formAction('admin-msg', () => api.auth('create_user', {
                username: $('new-u').value.trim(),
                password: $('new-p').value,
                role: $('new-r').value
            }), () => {
                $('new-u').value = '';
                $('new-p').value = '';
                this.pollData();
            });
        },

        async updateUserRole() {
            await this.formAction('role-msg', () => api.auth('update_user_role', {
                username: $('role-u').value.trim(),
                role: $('role-r').value
            }), () => this.pollData());
        },

        async changePw() {
            await this.formAction('pw-msg', () => api.auth('change_password', {
                old_password: $('pw-old').value,
                new_password: $('pw-new').value
            }), () => {
                $('pw-old').value = '';
                $('pw-new').value = '';
            });
        },

        async formAction(messageId, action, onSuccess) {
            const msg = $(messageId);
            msg.innerText = 'Working...';
            msg.style.color = 'var(--text-muted)';
            try {
                const data = await action();
                msg.innerText = data.message || 'Done.';
                msg.style.color = 'var(--online)';
                if (onSuccess) onSuccess(data);
            } catch (e) {
                msg.innerText = e.message;
                msg.style.color = 'var(--danger)';
            }
        },

        initAFK() {
            const rst = () => {
                if (!this.online) {
                    this.online = true;
                    this.currentPollInterval = this.minPollInterval;
                    $('my-status-dot').className = 'status-dot';
                    $('my-status-text').innerText = 'Online';
                    api.auth('heartbeat').catch(() => {});
                }
                clearTimeout(this.afkTimer);
                this.afkTimer = setTimeout(() => {
                    this.online = false;
                    this.currentPollInterval = this.maxPollInterval;
                    $('my-status-dot').className = 'status-dot offline';
                    $('my-status-text').innerText = 'AFK';
                }, 120000);
            };
            ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'].forEach(e => window.addEventListener(e, rst, {passive: true}));
            rst();
        },

        async logout() {
            await api.auth('logout').catch(() => {});
            window.location.reload();
        },

        scroll() {
            const b = $('chat-messages');
            b.scrollTop = b.scrollHeight;
        }
    };

    window.app = app;
    window.addEventListener('DOMContentLoaded', () => app.init());
})();
