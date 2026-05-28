/** Fired when LIVE panel toggles/refreshes a module (AppLayout). */
export const PLATFORM_NODES_CHANGED = 'platform:nodes-changed';

/** @param {Record<string, unknown>|null} payload Node status map from GET /dashboard/nodes/status */
export function dispatchNodesChanged(payload = null) {
    window.dispatchEvent(new CustomEvent(PLATFORM_NODES_CHANGED, { detail: payload }));
}

/** @param {(payload: Record<string, unknown>|null) => void} handler */
export function onNodesChanged(handler) {
    const listener = (event) => handler(event.detail ?? null);
    window.addEventListener(PLATFORM_NODES_CHANGED, listener);
    return () => window.removeEventListener(PLATFORM_NODES_CHANGED, listener);
}
