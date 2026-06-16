import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';
import type { TeamMember } from '@/types/models';

export interface AdminTeamMemberPayload {
  name: string;
  role?: string | null;
  bio?: string | null;
  photo_id?: number | null;
  email?: string | null;
  instagram_url?: string | null;
  status: 'draft' | 'published';
  sort_order?: number;
}

export interface AdminTeamMember extends TeamMember {
  photo_id: number | null;
}

export const adminTeamKeys = {
  all:  () => ['admin', 'team-members'] as const,
  list: () => [...adminTeamKeys.all(), 'list'] as const,
};

export function useAdminTeamMembers() {
  return useQuery({
    queryKey: adminTeamKeys.list(),
    queryFn:  async () => {
      const res = await apiClient.get<ApiResponse<AdminTeamMember[]>>('/admin/team-members');
      return res.data.data;
    },
    staleTime: 0,
  });
}

export function useCreateTeamMember() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminTeamMemberPayload) => {
      const res = await apiClient.post<ApiResponse<AdminTeamMember>>('/admin/team-members', payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminTeamKeys.all() }),
  });
}

export function useUpdateTeamMember(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (payload: AdminTeamMemberPayload) => {
      const res = await apiClient.put<ApiResponse<AdminTeamMember>>(`/admin/team-members/${id}`, payload);
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminTeamKeys.all() }),
  });
}

export function useDeleteTeamMember() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/team-members/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: adminTeamKeys.all() }),
  });
}
