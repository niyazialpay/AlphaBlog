// echo.js
import Echo from 'laravel-echo';

// Not: Reverb kullanıyorsan Pusher'a gerek yok. Pusher'ı kaldır.
// import Pusher from 'pusher-js'; window.Pusher = Pusher;

const hasReverbEnv =
    typeof import.meta.env.VITE_REVERB_APP_KEY !== 'undefined' &&
    typeof import.meta.env.VITE_REVERB_HOST !== 'undefined';

try {
    if (hasReverbEnv) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
            wsPath: import.meta.env.VITE_REVERB_PATH || '',
        });
    } else {
        // Env yoksa sessizce geç (prod’da websockets kurulu değilse app çökmemeli)
        console.warn('[echo] Reverb env not set; Echo disabled.');
    }
} catch (e) {
    console.error('[echo] init failed:', e);
}
