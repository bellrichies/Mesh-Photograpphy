import { useQuery } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { DashboardMetrics } from '@/types/models';

export const adminDashboardKeys = {
  all:     () => ['admin', 'dashboard'] as const,
  metrics: () => [...adminDashboardKeys.all(), 'metrics'] as const,
};

async function fetchDashboardMetrics(): Promise<DashboardMetrics> {
  const res = await apiClient.get<ApiResponse<DashboardMetrics>>('/admin/dashboard');
  return res.data.data;
}

export function useAdminDashboard() {
  return useQuery({
    queryKey: adminDashboardKeys.metrics(),
    queryFn:  fetchDashboardMetrics,
    staleTime: 60 * 1000,
  });
}
