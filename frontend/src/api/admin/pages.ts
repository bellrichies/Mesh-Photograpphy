import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { CmsPage } from '@/types/models';

export interface AdminPageSectionPayload {
  section_key:  string;
  section_type: string;
  title?:       string | null;
  content?:     string | null;
  media_id?:    number | null;
  settings?:    Record<string, unknown>;
  sort_order:   number;
}

export interface AdminPagePayload {
  title:           string;
  slug:            string;
  template?:       string | null;
  body?:           string | null;
  is_published:    boolean;
  seo_title?:      string | null;
  seo_description?: string | null;
  canonical_url?:  string | null;
  og_title?:       string | null;
  og_description?: string | null;
  og_image_id?:    number | null;
  seo_robots?:     string | null;
  schema_markup?:  string | null;
  sections?:       AdminPageSectionPayload[];
}

export interface AdminPage extends CmsPage {
  body?: string | null;
  status: 'published' | 'draft';
  is_published?: boolean;
  created_at: string;
  updated_at: string;
}

export const adminPageKeys = {
  all:    () => ['admin', 'pages'] as const,
  list:   () => [...adminPageKeys.all(), 'list'] as const,
  detail: (id: number) => [...adminPageKeys.all(), 'detail', id] as const,
};

export function useAdminPages() {
  return useQuery({
    queryKey: adminPageKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminPage[]>>('/admin/pages');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useAdminPage(id: number) {
  return useQuery({
    queryKey: adminPageKeys.detail(id),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminPage>>(`/admin/pages/${id}`);
      return res.data.data;
    },
    enabled: !!id,
    staleTime: 0,
  });
}

export function useCreatePage() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminPagePayload) => {
      const res = await apiClient.post<ApiResponse<AdminPage>>('/admin/pages', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminPageKeys.all() }),
  });
}

export function useUpdatePage(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminPagePayload) => {
      const res = await apiClient.put<ApiResponse<AdminPage>>(`/admin/pages/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: adminPageKeys.all() });
      qc.invalidateQueries({ queryKey: adminPageKeys.detail(id) });
    },
  });
}

export function useDeletePage() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/pages/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminPageKeys.all() }),
  });
}
