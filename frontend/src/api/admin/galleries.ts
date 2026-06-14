import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { Gallery, MediaRecord } from '@/types/models';

export interface AdminGalleryFilters {
  status?: 'published' | 'draft';
  q?: string;
  page?: number;
  per_page?: number;
}

export interface AdminGalleryPayload {
  title: string;
  slug: string;
  description?: string | null;
  category?: string | null;
  is_published: boolean;
  is_featured: boolean;
  cover_image_id?: number | null;
  sort_order?: number;
  seo_title?: string | null;
  seo_description?: string | null;
}

export interface AdminGallery extends Gallery {
  category: string | null;
  seo_title: string | null;
  seo_description: string | null;
  cover_image_id: number | null;
}

export interface AdminGalleryDetail extends AdminGallery {
  media: (MediaRecord & { sort_order: number; caption: string | null })[];
}

export const adminGalleryKeys = {
  all:    () => ['admin', 'galleries'] as const,
  list:   (f: AdminGalleryFilters) => [...adminGalleryKeys.all(), 'list', f] as const,
  detail: (id: number) => [...adminGalleryKeys.all(), 'detail', id] as const,
};

export function useAdminGalleries(filters: AdminGalleryFilters = {}) {
  return useQuery({
    queryKey: adminGalleryKeys.list(filters),
    queryFn:  async () => {
      const res = await apiClient.get<PaginatedResponse<AdminGallery>>('/admin/galleries', { params: filters });
      return res.data;
    },
    staleTime: 0,
  });
}

export function useAdminGallery(id: number) {
  return useQuery({
    queryKey: adminGalleryKeys.detail(id),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminGalleryDetail>>(`/admin/galleries/${id}`);
      return res.data.data;
    },
    enabled: !!id,
    staleTime: 0,
  });
}

export function useCreateGallery() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminGalleryPayload) => {
      const res = await apiClient.post<ApiResponse<AdminGallery>>('/admin/galleries', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminGalleryKeys.all() }),
  });
}

export function useUpdateGallery(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminGalleryPayload) => {
      const res = await apiClient.put<ApiResponse<AdminGallery>>(`/admin/galleries/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: adminGalleryKeys.all() });
      qc.invalidateQueries({ queryKey: adminGalleryKeys.detail(id) });
    },
  });
}

export function useDeleteGallery() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/galleries/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminGalleryKeys.all() }),
  });
}

export function useAttachGalleryMedia(galleryId: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: { media_id: number; caption?: string }) => {
      await apiClient.post(`/admin/galleries/${galleryId}/media`, payload);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminGalleryKeys.detail(galleryId) }),
  });
}

export function useRemoveGalleryMedia(galleryId: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (mediaId: number) => {
      await apiClient.delete(`/admin/galleries/${galleryId}/media/${mediaId}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminGalleryKeys.detail(galleryId) }),
  });
}

export function useReorderGalleryMedia(galleryId: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (order: number[]) => {
      await apiClient.post(`/admin/galleries/${galleryId}/media/reorder`, { order });
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminGalleryKeys.detail(galleryId) }),
  });
}
