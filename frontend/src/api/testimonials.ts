import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { Testimonial } from '@/types/models';

export const testimonialKeys = {
  all:  () => ['testimonials'] as const,
  list: () => [...testimonialKeys.all(), 'list'] as const,
};

async function fetchTestimonials(): Promise<Testimonial[]> {
  const res = await apiClient.get<ApiResponse<Testimonial[]>>('/testimonials');
  return res.data.data;
}

export function useTestimonials() {
  return useQuery({
    queryKey: testimonialKeys.list(),
    queryFn:  fetchTestimonials,
    staleTime: 10 * 60 * 1000,
  });
}
