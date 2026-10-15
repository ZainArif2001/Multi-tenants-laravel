import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// wsHost = current hostname so it works on every tenant subdomain
// (democo.localhost, faruknew.localhost, ...) — Reverb accepts by app key.
// Port/key come from server-rendered window.__reverb (Blade), env is fallback.
const r = window.__reverb || {};

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: r.key || import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: r.port ?? import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: r.port ?? import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (r.scheme || import.meta.env.VITE_REVERB_SCHEME) === 'https',
    enabledTransports: ['ws', 'wss'],
});
