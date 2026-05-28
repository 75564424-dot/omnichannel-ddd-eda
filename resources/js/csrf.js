/**
 * CSRF helpers for axios/fetch on multi-instance local dev (per-port session + XSRF cookies).
 */
export function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : null;
}

export function xsrfCookieName() {
    return document.querySelector('meta[name="xsrf-cookie"]')?.getAttribute('content') || 'XSRF-TOKEN';
}

export function csrfTokenFromMeta() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

/** @param {import('axios').InternalAxiosRequestConfig} config */
export function applyAxiosCsrfHeaders(config) {
    const metaCsrf = csrfTokenFromMeta();
    if (metaCsrf) {
        if (typeof config.headers?.set === 'function') {
            config.headers.set('X-CSRF-TOKEN', metaCsrf);
            config.headers.delete('X-XSRF-TOKEN');
        } else {
            config.headers['X-CSRF-TOKEN'] = metaCsrf;
            delete config.headers['X-XSRF-TOKEN'];
        }
        return config;
    }

    const xsrf = readCookie(xsrfCookieName());
    if (xsrf) {
        if (typeof config.headers?.set === 'function') {
            config.headers.set('X-XSRF-TOKEN', xsrf);
        } else {
            config.headers['X-XSRF-TOKEN'] = xsrf;
        }
    }
    return config;
}

export function syncCsrfFromInertiaPage(page) {
    const token = page?.props?.csrf?.token;
    const cookie = page?.props?.csrf?.cookie;
    if (typeof token === 'string' && token !== '') {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.setAttribute('content', token);
        }
        if (window.axios?.defaults?.headers?.common) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
    }
    if (typeof cookie === 'string' && cookie !== '') {
        const xsrfMeta = document.querySelector('meta[name="xsrf-cookie"]');
        if (xsrfMeta) {
            xsrfMeta.setAttribute('content', cookie);
        }
    }
}
