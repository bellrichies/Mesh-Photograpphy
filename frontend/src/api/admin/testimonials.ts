import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { Testimonial } from '@/types/models';

export interface AdminTestimonialPayload {
  client_name: string;
  client_role?: string | null;
  body: string;
  rating?: number;
  portrait_id?: number | null;
  status: 'draft' | 'published';
  sort_order?: number;
}

export interface AdminTestimonial extends Testimonial {
  portrait_id: number | null;
}

export const adminTestimonialKeys = {
  all:  () => ['admin', 'testimonials'] as const,
  list: () => [...adminTestimonialKeys.all(), 'list'] as const,
};

export function useAdminTestimonials() {
  return useQuery({
    queryKey: adminTestimonialKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminTestimonial[]>>('/admin/testimonials');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useCreateTestimonial() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminTestimonialPayload) => {
      const res = await apiClient.post<ApiResponse<AdminTestimonial>>('/admin/testimonials', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminTestimonialKeys.all() }),
  });
}

export function useUpdateTestimonial(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminTestimonialPayload) => {
      const res = await apiClient.put<ApiResponse<AdminTestimonial>>(`/admin/testimonials/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminTestimonialKeys.all() }),
  });
}

export function useDeleteTestimonial() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/testimonials/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminTestimonialKeys.all() }),
  });
}
