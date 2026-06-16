import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';
import type { TeamMember } from '@/types/models';

export const teamKeys = {
  all:  () => ['team-members'] as const,
  list: () => [...teamKeys.all(), 'list'] as const,
};

async function fetchTeamMembers(): Promise<TeamMember[]> {
  const res = await apiClient.get<ApiResponse<TeamMember[]>>('/team-members');
  return res.data.data;
}

export function useTeamMembers() {
  return useQuery({
    queryKey: teamKeys.list(),
    queryFn:  fetchTeamMembers,
    staleTime: 10 * 60 * 1000,
  });
}
