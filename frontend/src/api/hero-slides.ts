import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { HeroSlide } from '@/types/models';

export const heroSlideKeys = {
  all:  () => ['hero-slides'] as const,
  list: () => [...heroSlideKeys.all(), 'list'] as const,
};

async function fetchHeroSlides(): Promise<HeroSlide[]> {
  const res = await apiClient.get<ApiResponse<HeroSlide[]>>('/hero-slides');
  return res.data.data;
}

export function useHeroSlides() {
  return useQuery({
    queryKey: heroSlideKeys.list(),
    queryFn:  fetchHeroSlides,
    staleTime: 10 * 60 * 1000,
  });
}
