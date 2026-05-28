/**
 * k6 sustained publish load test — Plan_Calidad Fase 2.
 * Target: 100 eps for configurable duration (default 60s).
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { uuidv4 } from 'https://jslib.k6.io/k6-utils/1.4.0/index.js';

const baseUrl = __ENV.APP_URL || 'http://127.0.0.1:8000';
const rate = Number(__ENV.PLATFORM_LOAD_TEST_EPS || 100);
const duration = `${__ENV.PLATFORM_LOAD_TEST_DURATION || 60}s`;
const apiKey = __ENV.PLATFORM_API_KEY || '';
const eventType = __ENV.PLATFORM_LOAD_TEST_EVENT_TYPE || 'Platform.Quality.LoadTest';

export const options = {
  scenarios: {
    sustained_publish: {
      executor: 'constant-arrival-rate',
      rate,
      timeUnit: '1s',
      duration,
      preAllocatedVUs: Math.min(rate, 100),
      maxVUs: Math.max(rate * 2, 50),
    },
  },
  thresholds: {
    http_req_failed: [`rate<${__ENV.PLATFORM_LOAD_TEST_MAX_ERROR_RATE || 0.05}`],
    http_req_duration: [`p(95)<${__ENV.PLATFORM_LOAD_TEST_P95_MS || 2000}`],
  },
};

export default function () {
  const eventId = uuidv4();
  const occurred = new Date().toISOString();
  const payload = JSON.stringify({
    event_id: eventId,
    event_type: eventType,
    occurred_at: occurred,
    origin: 'LoadTest',
    payload: {
      event_id: eventId,
      event: eventType,
      occurred_at: occurred,
      seq: __ITER,
    },
  });

  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  };
  if (apiKey) {
    headers['X-API-Key'] = apiKey;
  }

  const res = http.post(`${baseUrl}/api/middleware/events/publish`, payload, { headers });

  check(res, {
    'status 201 or 200': (r) => r.status === 201 || r.status === 200,
  });

  sleep(0.01);
}
