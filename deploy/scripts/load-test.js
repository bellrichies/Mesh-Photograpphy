/**
 * Mesh Photography — k6 load test (PL-10)
 * Target: 100 concurrent users for 60 seconds
 *
 * Install k6: https://k6.io/docs/get-started/installation/
 * Run:  k6 run deploy/scripts/load-test.js --env BASE_URL=https://meshphoto.com
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

const errorRate      = new Rate('error_rate');
const apiLatency     = new Trend('api_latency_ms', true);
const notFoundErrors = new Counter('not_found_errors');

export const options = {
  stages: [
    { duration: '15s', target: 20  }, // ramp up
    { duration: '30s', target: 100 }, // stay at 100 concurrent
    { duration: '15s', target: 0   }, // ramp down
  ],
  thresholds: {
    http_req_failed:   ['rate<0.01'],   // < 1% error rate
    http_req_duration: ['p(95)<500'],   // 95th percentile < 500ms
    error_rate:        ['rate<0.01'],
  },
};

const publicEndpoints = [
  '/api/v1/health',
  '/api/v1/settings/public',
  '/api/v1/hero-slides',
  '/api/v1/galleries?page=1&per_page=12',
  '/api/v1/galleries/categories',
  '/api/v1/services',
  '/api/v1/blog/posts?page=1&per_page=10',
  '/api/v1/blog/categories',
  '/api/v1/testimonials',
];

export default function () {
  const endpoint = publicEndpoints[Math.floor(Math.random() * publicEndpoints.length)];
  const url      = `${BASE_URL}${endpoint}`;

  const res = http.get(url, {
    headers: { Accept: 'application/json' },
    timeout: '10s',
  });

  apiLatency.add(res.timings.duration);

  const ok = check(res, {
    'status is 200':        (r) => r.status === 200,
    'has data envelope':    (r) => {
      try {
        const body = JSON.parse(r.body);
        return body !== null && ('data' in body || 'message' in body);
      } catch {
        return false;
      }
    },
    'response under 500ms': (r) => r.timings.duration < 500,
  });

  if (!ok) {
    errorRate.add(1);
  }

  if (res.status === 404) {
    notFoundErrors.add(1);
  }

  sleep(Math.random() * 2 + 0.5); // 0.5–2.5s think time between requests
}

export function handleSummary(data) {
  return {
    'deploy/scripts/load-test-result.json': JSON.stringify(data, null, 2),
    stdout: textSummary(data, { indent: ' ', enableColors: true }),
  };
}

function textSummary(data, opts) {
  const { metrics } = data;
  const dur  = metrics.http_req_duration;
  const fail = metrics.http_req_failed;

  return [
    '',
    '── Mesh Photography Load Test Summary ─────────────────────────────',
    `  Requests:       ${metrics.http_reqs?.values?.count ?? 'N/A'}`,
    `  Error rate:     ${((fail?.values?.rate ?? 0) * 100).toFixed(2)}%`,
    `  Latency p50:    ${dur?.values?.['p(50)']?.toFixed(1) ?? 'N/A'} ms`,
    `  Latency p95:    ${dur?.values?.['p(95)']?.toFixed(1) ?? 'N/A'} ms`,
    `  Latency p99:    ${dur?.values?.['p(99)']?.toFixed(1) ?? 'N/A'} ms`,
    '────────────────────────────────────────────────────────────────────',
    '',
  ].join('\n');
}
