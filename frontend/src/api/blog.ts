import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { BlogPost, BlogPostDetail, BlogCategory, BlogTag } from '@/types/models';

export interface BlogFilters {
  category?: string;
  tag?: string;
  q?: string;
  page?: number;
  per_page?: number;
}

export const blogKeys = {
  all:        () => ['blog'] as const,
  categories: () => [...blogKeys.all(), 'categories'] as const,
  tags:       () => [...blogKeys.all(), 'tags'] as const,
  list:       (f: BlogFilters) => [...blogKeys.all(), 'list', f] as const,
  detail:     (slug: string) => [...blogKeys.all(), 'detail', slug] as const,
};

async function fetchPosts(filters: BlogFilters): Promise<PaginatedResponse<BlogPost>> {
  const res = await apiClient.get<PaginatedResponse<BlogPost>>('/blog/posts', { params: filters });
  return res.data;
}

async function fetchPost(slug: string): Promise<BlogPostDetail> {
  const res = await apiClient.get<ApiResponse<BlogPostDetail>>(`/blog/posts/${slug}`);
  return res.data.data;
}

async function fetchCategories(): Promise<BlogCategory[]> {
  const res = await apiClient.get<ApiResponse<BlogCategory[]>>('/blog/categories');
  return res.data.data;
}

async function fetchTags(): Promise<BlogTag[]> {
  const res = await apiClient.get<ApiResponse<BlogTag[]>>('/blog/tags');
  return res.data.data;
}

export function useBlogPosts(filters: BlogFilters = {}) {
  return useQuery({
    queryKey: blogKeys.list(filters),
    queryFn:  () => fetchPosts(filters),
    staleTime: 5 * 60 * 1000,
  });
}

export function useBlogPost(slug: string) {
  return useQuery({
    queryKey: blogKeys.detail(slug),
    queryFn:  () => fetchPost(slug),
    staleTime: 5 * 60 * 1000,
    enabled:  !!slug,
  });
}

export function useBlogCategories() {
  return useQuery({
    queryKey: blogKeys.categories(),
    queryFn:  fetchCategories,
    staleTime: 10 * 60 * 1000,
  });
}

export function useBlogTags() {
  return useQuery({
    queryKey: blogKeys.tags(),
    queryFn:  fetchTags,
    staleTime: 10 * 60 * 1000,
  });
}
