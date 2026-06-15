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
type QueueItem = { resolve: (token: string) => void; reject: (err: unknown) => void };
let refreshQueue: QueueItem[] = [];
let onRefreshFailed: (() => void) | null = null;

export const setOnRefreshFailed = (cb: () => void): void => {
  onRefreshFailed = cb;
};

const processQueue = (err: unknown, token: string | null) => {
  refreshQueue.forEach(({ resolve, reject }) => {
    if (err) reject(err);
    else if (token) resolve(token);
  });
  refreshQueue = [];
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
      // Don't attempt refresh when there's no session (accessToken is null — user not logged in),
      // or when the failing request was the refresh endpoint itself (prevent infinite loop).
      if (!accessToken || original.url?.includes('/auth/refresh')) {
        if (original.url?.includes('/auth/refresh')) onRefreshFailed?.();
        return Promise.reject(error);
      }

      // If a refresh is already in flight, queue this request to retry once it resolves.
      if (isRefreshing) {
        return new Promise<string>((resolve, reject) => {
          refreshQueue.push({ resolve, reject });
        }).then((token) => {
          if (original.headers) original.headers.Authorization = `Bearer ${token}`;
          return apiClient(original);
        });
      }

      original._retry = true;
      isRefreshing = true;

      try {
        const newToken = await refreshAccessToken();
        processQueue(null, newToken);
        if (original.headers) original.headers.Authorization = `Bearer ${newToken}`;
        return apiClient(original);
      } catch (refreshError) {
        processQueue(refreshError, null);
        onRefreshFailed?.();
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
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
