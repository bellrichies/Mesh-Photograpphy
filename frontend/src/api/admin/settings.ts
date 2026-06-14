import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';

export type AdminSettingsData = Record<string, Record<string, string>>;

export const adminSettingsKeys = {
  all:  () => ['admin', 'settings'] as const,
  list: () => [...adminSettingsKeys.all(), 'list'] as const,
};

export function useAdminSettings() {
  return useQuery({
    queryKey: adminSettingsKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminSettingsData>>('/admin/settings');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useUpdateSettings() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (data: AdminSettingsData) => {
      await apiClient.put('/admin/settings', data);
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: adminSettingsKeys.all() });
      qc.invalidateQueries({ queryKey: ['settings'] });
    },
  });
}
