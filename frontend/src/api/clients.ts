import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { Client } from '@/types/models';

export const clientKeys = {
  all:  () => ['clients'] as const,
  list: () => [...clientKeys.all(), 'list'] as const,
};

async function fetchClients(): Promise<Client[]> {
  const res = await apiClient.get<ApiResponse<Client[]>>('/clients');
  return res.data.data;
}

export function useClients() {
  return useQuery({
    queryKey: clientKeys.list(),
    queryFn:  fetchClients,
    staleTime: 10 * 60 * 1000,
  });
}
