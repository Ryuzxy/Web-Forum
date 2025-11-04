// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Add Pusher to window object
window.Pusher = Pusher;

// Initialize Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    enabledTransports: ['ws', 'wss']
});

console.log('Echo initialized');