<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.4);
        --glass-border: rgba(255, 255, 255, 0.5);
        --text-main: #2d3748; --text-muted: #4a5568; --accent: #4299e1;
        --msg-sent: rgba(66, 153, 225, 0.8); --msg-recv: rgba(255, 255, 255, 0.6);
        --online: #48bb78; --offline: #a0aec0; --nav-bg: rgba(255, 255, 255, 0.6);
    }
    .dark-mode {
        --glass-bg: rgba(15, 23, 42, 0.6);
        --glass-border: rgba(255, 255, 255, 0.1);
        --text-main: #f8fafc; --text-muted: #cbd5e1; --accent: #00f0ff;
        --msg-sent: rgba(0, 240, 255, 0.3); --msg-recv: rgba(30, 41, 59, 0.8);
        --nav-bg: rgba(15, 23, 42, 0.8);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
    body { background: transparent; color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    
    #app-container { width: 100%; max-width: 425px; height: 95vh; background: var(--glass-bg); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border); border-radius: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.4); overflow: hidden; display: flex; flex-direction: column; position: relative; }
    .view { display: none; height: 100%; flex-direction: column; position: relative; }
    .view.active { display: flex; animation: fadeIn 0.4s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }

    select, input, button, textarea { width: 100%; padding: 1rem; margin-bottom: 1rem; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255, 255, 255, 0.1); color: var(--text-main); font-size: 1rem; outline: none; backdrop-filter: blur(4px); }
    select option { background: #0f172a; color: #fff; }
    button { background: var(--accent); color: #fff; font-weight: bold; cursor: pointer; border: none; transition: all 0.3s ease; }
    button:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0, 240, 255, 0.4); }
    .btn-snippet { background: transparent !important; border: 1px solid var(--accent) !important; color: var(--accent) !important; }

    .login-wrapper { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 2rem; text-align: center; }
    .logo { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--accent); text-shadow: 0 0 15px rgba(0, 240, 255, 0.5); letter-spacing: 2px; }
    
    .directory-card { background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 16px; padding: 1rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; cursor: pointer; transition: 0.3s; }
    .directory-card:hover { background: rgba(0,240,255,0.1); border-color: var(--accent); }
    .dir-info { display: flex; align-items: center; gap: 15px; }
    .dir-avatar { width: 50px; height: 50px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #fff; font-size: 1.2rem; }

    .chat-header { padding: 1rem; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.05); }
    .status-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--online); box-shadow: 0 0 8px var(--online); transition: 0.3s; }
    .status-dot.offline { background: var(--offline); box-shadow: none; }
    .chat-messages { flex: 1; padding: 1rem; padding-bottom: 90px; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; }
    .message { max-width: 80%; padding: 0.8rem 1rem; border-radius: 16px; line-height: 1.4; animation: slideIn 0.3s forwards; }
    @keyframes slideIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    .msg-sent { align-self: flex-end; background: var(--msg-sent); border-bottom-right-radius: 4px; }
    .msg-recv { align-self: flex-start; background: var(--msg-recv); border-bottom-left-radius: 4px; border: 1px solid var(--glass-border); }
    .chat-input { position: absolute; bottom: 80px; left: 0; right: 0; padding: 0.8rem 1rem; border-top: 1px solid var(--glass-border); display: flex; gap: 10px; background: var(--glass-bg); backdrop-filter: blur(10px); }
    .chat-input input { margin: 0; flex: 1; } .chat-input button { margin: 0; width: auto; padding: 0 1.2rem; }
    .file-card { display: inline-block; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 8px; color: var(--accent); text-decoration: none; border: 1px solid var(--accent); margin-top: 5px; }

    .bottom-nav { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); width: 90%; background: var(--nav-bg); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 30px; display: flex; justify-content: space-around; padding: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); z-index: 50; }
    .nav-item { color: var(--text-muted); cursor: pointer; padding: 10px; border-radius: 50%; transition: 0.3s; display: flex; align-items: center; justify-content: center; width: 45px; height: 45px; }
    .nav-item.active { color: var(--accent); background: rgba(0, 240, 255, 0.1); box-shadow: 0 0 15px rgba(0,240,255,0.2); }
    .page-content { flex: 1; padding: 2rem 1.5rem; overflow-y: auto; padding-bottom: 90px; }
    .page-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 1.5rem; color: var(--accent); border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem; }
    .modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 100; }
    .modal-overlay.active { display: flex; } .modal-content { background: var(--glass-bg); padding: 1.5rem; border-radius: 16px; width: 90%; }
</style>