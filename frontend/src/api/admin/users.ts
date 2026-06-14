import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { AdminUser } from '@/types/models';

export interface AdminUserPayload {
  email?: string;
  first_name: string;
  last_name: string;
  password?: string;
  status?: 'active' | 'inactive';
  role_ids?: number[];
}

export const adminUserKeys = {
  all:  () => ['admin', 'users'] as const,
  list: () => [...adminUserKeys.all(), 'list'] as const,
};

export function useAdminUsers() {
  return useQuery({
    queryKey: adminUserKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminUser[]>>('/admin/users');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useCreateUser() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminUserPayload & { email: string; password: string }) => {
      const res = await apiClient.post<ApiResponse<AdminUser>>('/admin/users', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminUserKeys.all() }),
  });
}

export function useUpdateUser(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminUserPayload) => {
      const res = await apiClient.put<ApiResponse<AdminUser>>(`/admin/users/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminUserKeys.all() }),
  });
}

export function useDeleteUser() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/users/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminUserKeys.all() }),
  });
}
