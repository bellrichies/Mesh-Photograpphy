import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { Inquiry } from '@/types/models';

export interface AdminInquiryFilters {
  status?: string;
  page?: number;
  per_page?: number;
}

export const adminInquiryKeys = {
  all:    () => ['admin', 'inquiries'] as const,
  list:   (f: AdminInquiryFilters) => [...adminInquiryKeys.all(), 'list', f] as const,
  detail: (id: number) => [...adminInquiryKeys.all(), 'detail', id] as const,
};

export function useAdminInquiries(filters: AdminInquiryFilters = {}) {
  return useQuery({
    queryKey: adminInquiryKeys.list(filters),
    queryFn:  async () => {
      const res = await apiClient.get<PaginatedResponse<Inquiry>>('/admin/inquiries', { params: filters });
      return res.data;
    },
    staleTime: 0,
  });
}

export function useAdminInquiry(id: number) {
  return useQuery({
    queryKey: adminInquiryKeys.detail(id),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<Inquiry>>(`/admin/inquiries/${id}`);
      return res.data.data;
    },
    enabled: !!id,
    staleTime: 0,
  });
}

export function useUpdateInquiryStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, status }: { id: number; status: string }) => {
      const res = await apiClient.patch<ApiResponse<{ id: number; status: string }>>(
        `/admin/inquiries/${id}/status`, { status }
      );
      return res.data.data;
    },
    onSuccess: (_data, { id }) => {
      qc.invalidateQueries({ queryKey: adminInquiryKeys.all() });
      qc.invalidateQueries({ queryKey: adminInquiryKeys.detail(id) });
    },
  });
}

export function useAddInquiryNote() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, note }: { id: number; note: string }) => {
      await apiClient.post(`/admin/inquiries/${id}/notes`, { note });
    },
    onSuccess: (_data, { id }) => {
      qc.invalidateQueries({ queryKey: adminInquiryKeys.detail(id) });
    },
  });
}
