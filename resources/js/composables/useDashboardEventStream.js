/**
 * SSE push for dashboard event feed — complements polling for nodes/metrics.
 */
export function useDashboardEventStream({ onFeedEvent, onActivity }) {
  let source = null;
  let lastEventId = 0;
  let reconnectTimer = null;

  function connect() {
    if (typeof window === 'undefined' || typeof EventSource === 'undefined') {
      return;
    }

    disconnect(false);

    const url = lastEventId > 0
      ? `/api/dashboard/stream?last_id=${lastEventId}`
      : '/api/dashboard/stream';

    source = new EventSource(url);

    source.addEventListener('event_feed', (event) => {
      try {
        const payload = JSON.parse(event.data);
        const id = Number(payload?.id ?? event.lastEventId ?? 0);
        if (id > lastEventId) {
          lastEventId = id;
        }
        if (typeof onFeedEvent === 'function') {
          onFeedEvent(payload);
        }
        if (typeof onActivity === 'function') {
          onActivity();
        }
      } catch (err) {
        console.error('Dashboard SSE parse error:', err);
      }
    });

    source.addEventListener('reconnect', () => {
      scheduleReconnect(500);
    });

    source.onerror = () => {
      scheduleReconnect(3000);
    };
  }

  function scheduleReconnect(delayMs) {
    disconnect(false);
    clearTimeout(reconnectTimer);
    reconnectTimer = setTimeout(connect, delayMs);
  }

  function disconnect(clearReconnect = true) {
    if (source) {
      source.close();
      source = null;
    }
    if (clearReconnect) {
      clearTimeout(reconnectTimer);
      reconnectTimer = null;
    }
  }

  return { connect, disconnect };
}
