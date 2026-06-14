import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { Gallery, GalleryCategory, GalleryDetail } from '@/types/models';

export interface GalleryFilters {
  category?: string;
  featured?: boolean;
  page?: number;
  per_page?: number;
}

export const galleryKeys = {
  all:        () => ['galleries'] as const,
  categories: () => [...galleryKeys.all(), 'categories'] as const,
  list:       (f: GalleryFilters) => [...galleryKeys.all(), 'list', f] as const,
  detail:     (slug: string) => [...galleryKeys.all(), 'detail', slug] as const,
};

async function fetchGalleries(filters: GalleryFilters): Promise<PaginatedResponse<Gallery>> {
  const res = await apiClient.get<PaginatedResponse<Gallery>>('/galleries', { params: filters });
  return res.data;
}

async function fetchGalleryCategories(): Promise<GalleryCategory[]> {
  const res = await apiClient.get<ApiResponse<GalleryCategory[]>>('/galleries/categories');
  return res.data.data;
}

async function fetchGallery(slug: string): Promise<GalleryDetail> {
  const res = await apiClient.get<ApiResponse<GalleryDetail>>(`/galleries/${slug}`);
  return res.data.data;
}

export function useGalleries(filters: GalleryFilters = {}) {
  return useQuery({
    queryKey: galleryKeys.list(filters),
    queryFn:  () => fetchGalleries(filters),
    staleTime: 5 * 60 * 1000,
  });
}

export function useGalleryCategories() {
  return useQuery({
    queryKey: galleryKeys.categories(),
    queryFn:  fetchGalleryCategories,
    staleTime: 10 * 60 * 1000,
  });
}

export function useGallery(slug: string) {
  return useQuery({
    queryKey: galleryKeys.detail(slug),
    queryFn:  () => fetchGallery(slug),
    staleTime: 5 * 60 * 1000,
    enabled:  !!slug,
  });
}
