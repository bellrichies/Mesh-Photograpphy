import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { BookingRequest } from '@/types/models';

export interface AdminBookingFilters {
  status?: string;
  page?: number;
  per_page?: number;
}

export const adminBookingKeys = {
  all:    () => ['admin', 'bookings'] as const,
  list:   (f: AdminBookingFilters) => [...adminBookingKeys.all(), 'list', f] as const,
  detail: (id: number) => [...adminBookingKeys.all(), 'detail', id] as const,
};

export function useAdminBookings(filters: AdminBookingFilters = {}) {
  return useQuery({
    queryKey: adminBookingKeys.list(filters),
    queryFn:  async () => {
      const res = await apiClient.get<PaginatedResponse<BookingRequest>>('/admin/bookings', { params: filters });
      return res.data;
    },
    staleTime: 0,
  });
}

export function useAdminBooking(id: number) {
  return useQuery({
    queryKey: adminBookingKeys.detail(id),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<BookingRequest>>(`/admin/bookings/${id}`);
      return res.data.data;
    },
    enabled: !!id,
    staleTime: 0,
  });
}

export function useUpdateBookingStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, status }: { id: number; status: string }) => {
      const res = await apiClient.patch<ApiResponse<{ id: number; status: string }>>(
        `/admin/bookings/${id}/status`, { status }
      );
      return res.data.data;
    },
    onSuccess: (_data, { id }) => {
      qc.invalidateQueries({ queryKey: adminBookingKeys.all() });
      qc.invalidateQueries({ queryKey: adminBookingKeys.detail(id) });
    },
  });
}
