import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { HeroSlide } from '@/types/models';

export interface AdminHeroSlidePayload {
  title: string;
  subtitle?: string | null;
  background_image_id?: number | null;
  cta_label?: string | null;
  cta_url?: string | null;
  sort_order?: number;
  status: 'draft' | 'published';
}

export interface AdminHeroSlide extends HeroSlide {
  background_image_id: number | null;
}

export const adminHeroSlideKeys = {
  all:  () => ['admin', 'hero-slides'] as const,
  list: () => [...adminHeroSlideKeys.all(), 'list'] as const,
};

export function useAdminHeroSlides() {
  return useQuery({
    queryKey: adminHeroSlideKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminHeroSlide[]>>('/admin/hero-slides');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useCreateHeroSlide() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminHeroSlidePayload) => {
      const res = await apiClient.post<ApiResponse<AdminHeroSlide>>('/admin/hero-slides', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminHeroSlideKeys.all() }),
  });
}

export function useUpdateHeroSlide(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminHeroSlidePayload) => {
      const res = await apiClient.put<ApiResponse<AdminHeroSlide>>(`/admin/hero-slides/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminHeroSlideKeys.all() }),
  });
}

export function useDeleteHeroSlide() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/hero-slides/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminHeroSlideKeys.all() }),
  });
}
