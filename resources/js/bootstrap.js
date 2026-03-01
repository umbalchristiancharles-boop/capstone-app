import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Ensure Laravel CSRF token is sent on stateful requests from the SPA
const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');
if (tokenMeta) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.content;
}

// Send cookies (session / XSRF cookie) on cross-site requests when needed
window.axios.defaults.withCredentials = true;

// CSRF token is already loaded from meta tag above.
// The /sanctum/csrf-cookie call should only be made when actually needed
// (e.g., before first stateful request), not automatically on every page load.
