import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { PublicSettings } from '@/types/models';

export const settingsKeys = {
  all:    () => ['settings'] as const,
  public: () => [...settingsKeys.all(), 'public'] as const,
};

async function fetchPublicSettings(): Promise<PublicSettings> {
  const res = await apiClient.get<ApiResponse<PublicSettings>>('/settings/public');
  return res.data.data;
}

export function usePublicSettings() {
  return useQuery({
    queryKey: settingsKeys.public(),
    queryFn:  fetchPublicSettings,
    staleTime: 10 * 60 * 1000,
  });
}
