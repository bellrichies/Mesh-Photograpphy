import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { MediaRecord } from '@/types/models';

export interface AdminMediaFilters {
  type?: 'image' | 'video' | 'document';
  q?: string;
  page?: number;
  per_page?: number;
}

export const adminMediaKeys = {
  all:  () => ['admin', 'media'] as const,
  list: (f: AdminMediaFilters) => [...adminMediaKeys.all(), 'list', f] as const,
};

export function useAdminMedia(filters: AdminMediaFilters = {}) {
  return useQuery({
    queryKey: adminMediaKeys.list(filters),
    queryFn:  async () => {
      const res = await apiClient.get<PaginatedResponse<MediaRecord>>('/admin/media', { params: filters });
      return res.data;
    },
    staleTime: 0,
  });
}

export function useUploadMedia() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('file', file);
      const res = await apiClient.post<ApiResponse<MediaRecord>>('/admin/media/upload', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminMediaKeys.all() }),
  });
}

export function useUpdateMedia() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, ...payload }: { id: number; alt_text?: string; title?: string; caption?: string }) => {
      const res = await apiClient.put<ApiResponse<MediaRecord>>(`/admin/media/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminMediaKeys.all() }),
  });
}

export function useDeleteMedia() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/media/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminMediaKeys.all() }),
  });
}
