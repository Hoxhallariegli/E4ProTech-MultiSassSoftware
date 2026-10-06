import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Reverb WebSocket disabled - Pure Firebase FCM & FCM Data Messages used for Realtime.
 */
