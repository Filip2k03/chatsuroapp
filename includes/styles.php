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