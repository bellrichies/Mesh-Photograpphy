import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { AdminUser } from '@/types/models';

interface LoginResponse {
  access_token: string;
  expires_in: number;
  user: AdminUser;
}

interface RefreshResponse {
  access_token: string;
  expires_in: number;
  user: AdminUser;
}

export const authApi = {
  login: (credentials: { email: string; password: string }) =>
    apiClient
      .post<ApiResponse<LoginResponse>>('/auth/login', credentials)
      .then((res) => res.data),

  refresh: () =>
    apiClient
      .post<ApiResponse<RefreshResponse>>('/auth/refresh')
      .then((res) => res.data),

  logout: () =>
    apiClient
      .post<ApiResponse<null>>('/auth/logout')
      .then((res) => res.data),

  me: () =>
    apiClient
      .get<ApiResponse<AdminUser>>('/auth/me')
      .then((res) => res.data),

  requestPasswordReset: (email: string) =>
    apiClient
      .post<ApiResponse<null>>('/auth/password/request', { email })
      .then((res) => res.data),

  resetPassword: (token: string, password: string) =>
    apiClient
      .post<ApiResponse<null>>('/auth/password/reset', { token, password })
      .then((res) => res.data),
};
