import axios from 'axios';
import { applyAxiosCsrfHeaders, csrfTokenFromMeta } from './csrf';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

const initialCsrf = csrfTokenFromMeta();
if (initialCsrf) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = initialCsrf;
}

window.axios.interceptors.request.use((config) => applyAxiosCsrfHeaders(config));
