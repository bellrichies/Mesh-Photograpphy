import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse, PaginatedResponse } from '@/types/api';
import type { BlogPost, BlogCategory, BlogTag } from '@/types/models';

export interface AdminBlogFilters {
  status?: 'published' | 'draft';
  q?: string;
  page?: number;
  per_page?: number;
}

export interface AdminBlogPostPayload {
  title: string;
  slug: string;
  excerpt?: string | null;
  body?: string;
  category_id?: number | null;
  cover_image_id?: number | null;
  is_published: boolean;
  tag_ids?: number[];
  seo_title?: string | null;
  seo_description?: string | null;
}

export interface AdminBlogPost extends BlogPost {
  category_id: number | null;
  seo_title: string | null;
  seo_description: string | null;
}

export interface AdminBlogPostDetail extends AdminBlogPost {
  body: string;
  tags: BlogTag[];
}

export interface BlogRevision {
  id: number;
  title: string;
  saved_by: string | null;
  created_at: string;
}

export interface BlogRevisionDetail extends BlogRevision {
  body: string;
}

export const adminBlogKeys = {
  all:        () => ['admin', 'blog'] as const,
  posts:      (f: AdminBlogFilters) => [...adminBlogKeys.all(), 'posts', f] as const,
  post:       (id: number) => [...adminBlogKeys.all(), 'post', id] as const,
  categories: () => [...adminBlogKeys.all(), 'categories'] as const,
  tags:       () => [...adminBlogKeys.all(), 'tags'] as const,
  revisions:  (postId: number) => [...adminBlogKeys.all(), 'revisions', postId] as const,
};

export function useAdminBlogPosts(filters: AdminBlogFilters = {}) {
  return useQuery({
    queryKey: adminBlogKeys.posts(filters),
    queryFn:  async () => {
      const res = await apiClient.get<PaginatedResponse<AdminBlogPost>>('/admin/blog/posts', { params: filters });
      return res.data;
    },
    staleTime: 0,
  });
}

export function useAdminBlogPost(id: number) {
  return useQuery({
    queryKey: adminBlogKeys.post(id),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminBlogPostDetail>>(`/admin/blog/posts/${id}`);
      return res.data.data;
    },
    enabled: !!id,
    staleTime: 0,
  });
}

export function useAdminBlogCategories() {
  return useQuery({
    queryKey: adminBlogKeys.categories(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<BlogCategory[]>>('/admin/blog/categories');
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });
}

export function useAdminBlogTags() {
  return useQuery({
    queryKey: adminBlogKeys.tags(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<BlogTag[]>>('/admin/blog/tags');
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });
}

export function useCreateBlogPost() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminBlogPostPayload) => {
      const res = await apiClient.post<ApiResponse<AdminBlogPost>>('/admin/blog/posts', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminBlogKeys.all() }),
  });
}

export function useUpdateBlogPost(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminBlogPostPayload) => {
      const res = await apiClient.put<ApiResponse<AdminBlogPost>>(`/admin/blog/posts/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: adminBlogKeys.all() });
      qc.invalidateQueries({ queryKey: adminBlogKeys.post(id) });
    },
  });
}

export function useDeleteBlogPost() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/blog/posts/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminBlogKeys.all() }),
  });
}

export function useAutosaveBlogPost(id: number) {
  return useMutation({
    mutationFn: async (body: string) => {
      await apiClient.post(`/admin/blog/posts/${id}/autosave`, { body });
    },
  });
}

export function useAdminBlogRevisions(postId: number) {
  return useQuery({
    queryKey: adminBlogKeys.revisions(postId),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<BlogRevision[]>>(`/admin/blog/posts/${postId}/revisions`);
      return res.data.data;
    },
    enabled: !!postId,
    staleTime: 0,
  });
}

export function useRestoreBlogRevision(postId: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (revisionId: number) => {
      await apiClient.post(`/admin/blog/posts/${postId}/revisions/${revisionId}/restore`);
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: adminBlogKeys.post(postId) });
      qc.invalidateQueries({ queryKey: adminBlogKeys.revisions(postId) });
    },
  });
}

export function useCreateBlogCategory() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: { name: string; slug: string }) => {
      const res = await apiClient.post<ApiResponse<BlogCategory>>('/admin/blog/categories', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminBlogKeys.categories() }),
  });
}

export function useDeleteBlogCategory() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/blog/categories/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminBlogKeys.categories() }),
  });
}

export function useCreateBlogTag() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: { name: string; slug: string }) => {
      const res = await apiClient.post<ApiResponse<BlogTag>>('/admin/blog/tags', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminBlogKeys.tags() }),
  });
}

export function useDeleteBlogTag() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/blog/tags/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminBlogKeys.tags() }),
  });
}
