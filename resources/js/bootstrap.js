import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrf) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
}

function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : null;
}

window.axios.interceptors.request.use((config) => {
    const xsrf = readCookie('XSRF-TOKEN');
    if (xsrf && !config.headers['X-XSRF-TOKEN']) {
        config.headers['X-XSRF-TOKEN'] = xsrf;
    }
    return config;
});
