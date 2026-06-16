import { useQuery } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { PaginatedResponse } from '@/types/api';

export interface ActivityLogEntry {
  id: number;
  action: string;
  model_type: string | null;
  model_id: number | null;
  description: string | null;
  user: { id: number; name: string; email: string } | null;
  ip_address: string | null;
  user_agent: string | null;
  request_method: string | null;
  request_path: string | null;
  created_at: string;
}

export interface ActivityLogFilters {
  page?: number;
  per_page?: number;
  action?: string;
  model_type?: string;
  user_id?: number;
}

export const activityLogKeys = {
  all:  () => ['admin', 'activity-logs'] as const,
  list: (f: ActivityLogFilters) => [...activityLogKeys.all(), 'list', f] as const,
};

export function useActivityLogs(filters: ActivityLogFilters = {}) {
  return useQuery({
    queryKey: activityLogKeys.list(filters),
    queryFn:  async () => {
      const res = await apiClient.get<PaginatedResponse<ActivityLogEntry>>('/admin/activity-logs', {
        params: filters,
      });
      return res.data;
    },
    staleTime: 30_000,
  });
}
