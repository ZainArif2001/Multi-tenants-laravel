(function () {
    'use strict';

    var KEY = {{ Js::from($key) }};
    var TENANT = {{ Js::from($tenantName) }};
    var COLOR = {{ Js::from($color) }};
    var SUGGESTIONS = {{ Js::from($suggestions) }};

    var script = document.currentScript || document.querySelector('script[src*="/embed/' + KEY + '/chat.js"]');
    var BASE = script ? new URL(script.src).origin + '/embed/' + KEY : '/embed/' + KEY;
    var STORE = 'tc_sess_' + KEY;

    var sessionId = localStorage.getItem(STORE) || null;
    var open = false, busy = false, live = false, poll = null;
    var messages = [];

    // ---------- Styles ----------
    var css = document.createElement('style');
    css.textContent =
        '#tcw-btn{position:fixed;bottom:20px;right:20px;width:56px;height:56px;border-radius:50%;border:none;cursor:pointer;' +
        'background:linear-gradient(135deg,' + COLOR + ',' + COLOR + 'cc);color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.25);' +
        'display:flex;align-items:center;justify-content:center;z-index:999999;transition:transform .15s}' +
        '#tcw-btn:hover{transform:scale(1.08)}' +
        '#tcw-panel{position:fixed;bottom:88px;right:20px;width:min(380px,calc(100vw - 40px));height:480px;background:#fff;' +
        'border-radius:16px;box-shadow:0 16px 48px rgba(0,0,0,.2);display:none;flex-direction:column;overflow:hidden;' +
        'z-index:999999;font-family:system-ui,-apple-system,sans-serif;border:1px solid #e5e7eb}' +
        '#tcw-panel.on{display:flex}' +
        '#tcw-head{background:linear-gradient(90deg,' + COLOR + ',' + COLOR + 'dd);color:#fff;padding:12px 16px;display:flex;align-items:center;gap:10px}' +
        '#tcw-head .tcw-dot{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-weight:700}' +
        '#tcw-head .tcw-title{font-weight:600;font-size:14px}' +
        '#tcw-head .tcw-sub{font-size:11px;opacity:.85;display:flex;align-items:center;gap:4px}' +
        '#tcw-head .tcw-on{width:7px;height:7px;border-radius:50%;background:#4ade80;display:inline-block}' +
        '#tcw-head button{margin-left:auto;background:rgba(255,255,255,.15);border:none;color:#fff;width:26px;height:26px;border-radius:50%;cursor:pointer;font-size:14px}' +
        '#tcw-hand{background:rgba(255,255,255,.15);border:none;color:#fff;font-size:11px;border-radius:999px;padding:4px 10px;cursor:pointer}' +
        '#tcw-msgs{flex:1;overflow-y:auto;padding:14px;background:#f9fafb;display:flex;flex-direction:column;gap:10px}' +
        '.tcw-m{max-width:82%;padding:9px 14px;font-size:13.5px;line-height:1.45;border-radius:16px;white-space:pre-wrap;word-wrap:break-word}' +
        '.tcw-m.user{align-self:flex-end;background:' + COLOR + ';color:#fff;border-bottom-right-radius:4px}' +
        '.tcw-m.bot,.tcw-m.staff{align-self:flex-start;background:#fff;border:1px solid #e5e7eb;border-bottom-left-radius:4px;color:#1f2937}' +
        '.tcw-m.staff{background:#f0fdf4;border-color:#bbf7d0}' +
        '.tcw-m.staff::before{content:"AGENT";display:block;font-size:9px;font-weight:700;color:#16a34a;margin-bottom:2px}' +
        '.tcw-typing{display:flex;gap:5px;padding:12px 14px}' +
        '.tcw-typing span{width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:tcwb 1s infinite}' +
        '.tcw-typing span:nth-child(2){animation-delay:.15s}.tcw-typing span:nth-child(3){animation-delay:.3s}' +
        '@keyframes tcwb{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-5px)}}' +
        '#tcw-sugg{padding:10px 12px 4px;background:#fff;border-top:1px solid #f3f4f6;display:flex;flex-wrap:wrap;gap:6px}' +
        '#tcw-sugg button{font-size:11px;padding:6px 10px;border-radius:999px;border:1px solid ' + COLOR + '55;color:' + COLOR + ';background:#fff;cursor:pointer}' +
        '#tcw-sugg button:hover{background:' + COLOR + '0d}' +
        '#tcw-form{display:flex;gap:8px;padding:12px;border-top:1px solid #e5e7eb;background:#fff}' +
        '#tcw-in{flex:1;border:1px solid #d1d5db;border-radius:999px;padding:9px 16px;font-size:13.5px;outline:none}' +
        '#tcw-in:focus{border-color:' + COLOR + '}' +
        '#tcw-send{width:38px;height:38px;border-radius:50%;border:none;background:' + COLOR + ';color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}' +
        '#tcw-send:disabled{opacity:.4;cursor:default}';
    document.head.appendChild(css);

    // ---------- DOM ----------
    var btn = document.createElement('button');
    btn.id = 'tcw-btn';
    btn.setAttribute('aria-label', 'Chat with us');
    btn.innerHTML = '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';

    var panel = document.createElement('div');
    panel.id = 'tcw-panel';
    panel.innerHTML =
        '<div id="tcw-head"><div class="tcw-dot">' + TENANT.charAt(0).toUpperCase() + '</div>' +
        '<div><div class="tcw-title"></div><div class="tcw-sub"><span class="tcw-on"></span> Online — replies instantly</div></div>' +
        '<button id="tcw-hand" title="Talk to a human">Talk to human</button>' +
        '<button id="tcw-x" aria-label="Close">×</button></div>' +
        '<div id="tcw-msgs"></div>' +
        '<div id="tcw-sugg"></div>' +
        '<form id="tcw-form"><input id="tcw-in" maxlength="1000" placeholder="Type your message..." autocomplete="off">' +
        '<button id="tcw-send" type="submit"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="transform:rotate(-45deg)"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg></button></form>';
    panel.querySelector('.tcw-title').textContent = TENANT + ' Assistant';

    document.body.appendChild(btn);
    document.body.appendChild(panel);

    var msgsEl = panel.querySelector('#tcw-msgs');
    var suggEl = panel.querySelector('#tcw-sugg');
    var form = panel.querySelector('#tcw-form');
    var input = panel.querySelector('#tcw-in');

    // ---------- Helpers ----------
    function scroll() { msgsEl.scrollTop = msgsEl.scrollHeight; }

    function addMsg(role, text) {
        var el = document.createElement('div');
        el.className = 'tcw-m ' + (role === 'user' ? 'user' : role === 'staff' ? 'staff' : 'bot');
        el.textContent = text;
        msgsEl.appendChild(el);
        scroll();
        return el;
    }

    function typing(show) {
        var t = msgsEl.querySelector('.tcw-typing');
        if (show && ! t) {
            t = document.createElement('div');
            t.className = 'tcw-m bot tcw-typing';
            t.innerHTML = '<span></span><span></span><span></span>';
            msgsEl.appendChild(t);
        } else if (! show && t) {
            t.remove();
        }
        scroll();
    }

    function renderSuggestions() {
        suggEl.innerHTML = '';
        if (messages.length > 1 || busy) { suggEl.style.display = 'none'; return; }
        suggEl.style.display = 'flex';
        SUGGESTIONS.forEach(function (s) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = s;
            b.onclick = function () { input.value = s; send(); };
            suggEl.appendChild(b);
        });
    }

    function toggle() {
        open = ! open;
        panel.classList.toggle('on', open);
        if (open && messages.length === 0) {
            addMsg('bot', "Hi there! I'm the " + TENANT + " assistant. How can I help you today?");
            messages.push('x');
            renderSuggestions();
        }
        if (open) input.focus();
    }

    // ---------- Chat ----------
    async function send() {
        var text = input.value.trim();
        if (! text || busy) return;
        busy = true;
        input.value = '';
        suggEl.style.display = 'none';
        messages.push('x');
        addMsg('user', text);
        typing(true);

        try {
            var res = await fetch(BASE + '/chat/stream', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'text/event-stream' },
                body: JSON.stringify({ message: text, session_id: sessionId }),
            });

            typing(false);
            var bubble = addMsg('bot', '');
            var reader = res.body.getReader();
            var decoder = new TextDecoder();
            var buffer = '';

            while (true) {
                var chunk = await reader.read();
                if (chunk.done) break;
                buffer += decoder.decode(chunk.value, { stream: true });
                var lines = buffer.split('\n');
                buffer = lines.pop();
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i];
                    if (line.indexOf('data: ') !== 0) continue;
                    try {
                        var evt = JSON.parse(line.slice(6));
                        if (evt.token) { bubble.textContent += evt.token; scroll(); }
                        if (evt.session_id) { sessionId = evt.session_id; localStorage.setItem(STORE, sessionId); }
                    } catch (e) {}
                }
            }
            if (! bubble.textContent) bubble.textContent = 'Sorry, something went wrong. Please try again.';
        } catch (e) {
            typing(false);
            addMsg('bot', 'Network error — please check your connection and try again.');
        }
        busy = false;
        input.focus();
    }

    // ---------- Human handoff ----------
    async function requestHuman() {
        if (! sessionId) {
            addMsg('bot', 'Please send a message first so I can connect you.');
            return;
        }
        await fetch(BASE + '/chat/handoff', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ session_id: sessionId }),
        }).catch(function () {});
        addMsg('bot', "Connecting you to a team member — they'll reply here shortly. 💬");
        live = true;
        poll = setInterval(pollStaff, 8000);
    }

    async function pollStaff() {
        if (! sessionId) return;
        try {
            var res = await fetch(BASE + '/chat/feed?session_id=' + encodeURIComponent(sessionId));
            var data = await res.json();
            var staff = (data.messages || []).filter(function (m) { return m.role === 'staff'; });
            var known = msgsEl.querySelectorAll('.tcw-m.staff').length;
            staff.slice(known).forEach(function (m) { addMsg('staff', m.message); });
        } catch (e) {}
    }

    // ---------- Events ----------
    btn.addEventListener('click', toggle);
    panel.querySelector('#tcw-x').addEventListener('click', toggle);
    panel.querySelector('#tcw-hand').addEventListener('click', requestHuman);
    form.addEventListener('submit', function (e) { e.preventDefault(); send(); });
})();
