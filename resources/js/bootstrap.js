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

// If using Laravel Sanctum, request the CSRF cookie so the `XSRF-TOKEN` cookie is set.
// This ensures subsequent stateful requests from the SPA include the CSRF token.
try {
	// Only attempt when running in a browser environment
	if (typeof window !== 'undefined' && window.location) {
		window.axios.get('/sanctum/csrf-cookie').catch(() => {
			// ignore errors; some setups may not use sanctum
		});
	}
} catch (e) {
	// no-op
}
