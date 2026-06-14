import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { Service } from '@/types/models';

export interface AdminServicePayload {
  title: string;
  slug: string;
  short_description?: string | null;
  description?: string | null;
  price_display?: string | null;
  cover_image_id?: number | null;
  is_published: boolean;
  sort_order?: number;
  seo_title?: string | null;
  seo_description?: string | null;
}

export interface AdminService extends Service {
  description: string | null;
  cover_image_id: number | null;
  seo_title: string | null;
  seo_description: string | null;
}

export const adminServiceKeys = {
  all:    () => ['admin', 'services'] as const,
  list:   () => [...adminServiceKeys.all(), 'list'] as const,
  detail: (id: number) => [...adminServiceKeys.all(), 'detail', id] as const,
};

export function useAdminServices() {
  return useQuery({
    queryKey: adminServiceKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminService[]>>('/admin/services');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useAdminService(id: number) {
  return useQuery({
    queryKey: adminServiceKeys.detail(id),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminService>>(`/admin/services/${id}`);
      return res.data.data;
    },
    enabled: !!id,
    staleTime: 0,
  });
}

export function useCreateService() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminServicePayload) => {
      const res = await apiClient.post<ApiResponse<AdminService>>('/admin/services', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminServiceKeys.all() }),
  });
}

export function useUpdateService(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminServicePayload) => {
      const res = await apiClient.put<ApiResponse<AdminService>>(`/admin/services/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: adminServiceKeys.all() });
      qc.invalidateQueries({ queryKey: adminServiceKeys.detail(id) });
    },
  });
}

export function useDeleteService() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/services/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminServiceKeys.all() }),
  });
}
