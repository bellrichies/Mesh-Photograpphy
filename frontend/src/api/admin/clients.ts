import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { Client } from '@/types/models';

export interface AdminClientPayload {
  name: string;
  website_url?: string | null;
  logo_id?: number | null;
  status: 'draft' | 'published';
  sort_order?: number;
}

export interface AdminClient extends Client {
  logo_id: number | null;
}

export const adminClientKeys = {
  all:  () => ['admin', 'clients'] as const,
  list: () => [...adminClientKeys.all(), 'list'] as const,
};

export function useAdminClients() {
  return useQuery({
    queryKey: adminClientKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminClient[]>>('/admin/clients');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useCreateClient() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminClientPayload) => {
      const res = await apiClient.post<ApiResponse<AdminClient>>('/admin/clients', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminClientKeys.all() }),
  });
}

export function useUpdateClient(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminClientPayload) => {
      const res = await apiClient.put<ApiResponse<AdminClient>>(`/admin/clients/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminClientKeys.all() }),
  });
}

export function useDeleteClient() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/clients/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminClientKeys.all() }),
  });
}
