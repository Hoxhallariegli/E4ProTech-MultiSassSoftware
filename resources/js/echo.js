import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || 'e4protech_reverb_key';
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPort = import.meta.env.VITE_REVERB_PORT || (window.location.protocol === 'https:' ? 443 : 8080);
const isHttps = import.meta.env.VITE_REVERB_SCHEME === 'https' || window.location.protocol === 'https:';

console.log('📡 [REVERB WEBSOCKET INIT] Host:', reverbHost, 'Port:', reverbPort, 'Key:', reverbKey, 'Scheme:', isHttps ? 'wss' : 'ws');

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: isHttps,
        enabledTransports: ['ws', 'wss'],
    });

    if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
        window.Echo.connector.pusher.connection.bind('state_change', (states) => {
            console.log('📡 [REVERB STATE CHANGE]:', states.current, '(from:', states.previous, ')');
        });

        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ [REVERB CONNECTED] Realtime WebSocket connection established successfully!');
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ [REVERB CONNECTION ERROR]:', err);
        });
    }
} catch (e) {
    console.error('❌ [REVERB INIT ERROR]:', e);
}
