/** Fallback node rows when Inertia does not pass `system_module_rows` from the host config. */
export const SYSTEM_MODULE_ROWS = [
  { key: 'middleware', label: 'Event Bus' },
];

/**
 * Laravel / DB may serialize booleans as 0|1 JSON numbers; `0 !== false` would wrongly keep "enabled" in Vue.
 */
export function coerceMiddlewareEventsEnabled(value, defaultEnabled = false) {
  if (value === false || value === 0 || value === '0' || value === 'false') return false;
  if (value === true || value === 1 || value === '1' || value === 'true') return true;
  return defaultEnabled;
}

/** Normalizes backend node payload — supports nested objects or legacy string status only. */
export function parseSystemNode(payload, moduleKey) {
  const slot = payload?.[moduleKey];
  if (slot !== null && typeof slot === 'object') {
    return {
      status: String(slot.status ?? '—').toUpperCase(),
      middleware_events_enabled: coerceMiddlewareEventsEnabled(slot.middleware_events_enabled, false),
    };
  }
  return {
    status: String(slot ?? 'OFFLINE').toUpperCase(),
    middleware_events_enabled: false,
  };
}
