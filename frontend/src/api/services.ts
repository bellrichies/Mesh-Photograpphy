import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { Service, ServiceDetail } from '@/types/models';

export const serviceKeys = {
  all:    () => ['services'] as const,
  list:   () => [...serviceKeys.all(), 'list'] as const,
  detail: (slug: string) => [...serviceKeys.all(), 'detail', slug] as const,
};

async function fetchServices(): Promise<Service[]> {
  const res = await apiClient.get<ApiResponse<Service[]>>('/services');
  return res.data.data;
}

async function fetchService(slug: string): Promise<ServiceDetail> {
  const res = await apiClient.get<ApiResponse<ServiceDetail>>(`/services/${slug}`);
  return res.data.data;
}

export function useServices() {
  return useQuery({
    queryKey: serviceKeys.list(),
    queryFn:  fetchServices,
    staleTime: 10 * 60 * 1000,
  });
}

export function useService(slug: string) {
  return useQuery({
    queryKey: serviceKeys.detail(slug),
    queryFn:  () => fetchService(slug),
    staleTime: 10 * 60 * 1000,
    enabled:  !!slug,
  });
}
