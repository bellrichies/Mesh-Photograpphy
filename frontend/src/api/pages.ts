import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { CmsPage } from '@/types/models';

export const pageKeys = {
  all:    () => ['cms-pages'] as const,
  detail: (slug: string) => [...pageKeys.all(), slug] as const,
};

async function fetchPage(slug: string): Promise<CmsPage> {
  const res = await apiClient.get<ApiResponse<CmsPage>>(`/pages/${slug}`);
  return res.data.data;
}

export function useCmsPage(slug: string) {
  return useQuery({
    queryKey: pageKeys.detail(slug),
    queryFn:  () => fetchPage(slug),
    staleTime: 10 * 60 * 1000,
    enabled:  !!slug,
  });
}
