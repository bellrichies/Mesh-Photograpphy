import axios, { type AxiosError } from 'axios';
import { QueryClient } from '@tanstack/react-query';

// Access token stored in module memory — never localStorage or cookies
let accessToken: string | null = null;

export const setAccessToken = (token: string | null): void => {
  accessToken = token;
};

export const getAccessToken = (): string | null => accessToken;

// Single Axios instance for all API calls
export const apiClient = axios.create({
  baseURL: '/api/v1',
  withCredentials: true, // sends httpOnly refresh cookie automatically
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Attach access token to every request
apiClient.interceptors.request.use((config) => {
  const token = getAccessToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Single-flight refresh queue — prevents multiple concurrent refresh calls
let isRefreshing = false;
let refreshQueue: Array<(token: string) => void> = [];
let onRefreshFailed: (() => void) | null = null;

export const setOnRefreshFailed = (cb: () => void): void => {
  onRefreshFailed = cb;
};

const refreshAccessToken = async (): Promise<string> => {
  const res = await axios.post<{ ok: boolean; data: { access_token: string } }>(
    '/api/v1/auth/refresh',
    {},
    { withCredentials: true }
  );
  const newToken = res.data.data.access_token;
  setAccessToken(newToken);
  return newToken;
};

// 401 interceptor — attempt silent refresh, then retry original request
apiClient.interceptors.response.use(
  (res) => res,
  async (error: AxiosError) => {
    const original = error.config as typeof error.config & { _retry?: boolean };

    if (error.response?.status === 401 && original && !original._retry) {
      original._retry = true;

      if (!isRefreshing) {
        isRefreshing = true;
        try {
          const newToken = await refreshAccessToken();
          refreshQueue.forEach((resolve) => resolve(newToken));
          refreshQueue = [];
        } catch {
          refreshQueue = [];
          isRefreshing = false;
          onRefreshFailed?.();
          return Promise.reject(error);
        } finally {
          isRefreshing = false;
        }
      }

      return new Promise((resolve, reject) => {
        refreshQueue.push((token: string) => {
          if (original.headers) {
            original.headers.Authorization = `Bearer ${token}`;
          }
          resolve(apiClient(original));
        });
        // Handle if refresh fails while queued
        const originalReject = reject;
        refreshQueue.push = new Proxy(refreshQueue.push, {
          apply(target, thisArg, args) {
            if (args[0] === originalReject) return 0;
            return Reflect.apply(target, thisArg, args);
          },
        });
      });
    }

    return Promise.reject(error);
  }
);

// TanStack Query client
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      gcTime: 10 * 60 * 1000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});
