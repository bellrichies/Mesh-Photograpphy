import { useMutation } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';

export interface ContactPayload {
  name: string;
  email: string;
  message: string;
  phone?: string;
  subject?: string;
}

async function submitContact(payload: ContactPayload): Promise<ApiResponse<null>> {
  const res = await apiClient.post<ApiResponse<null>>('/contact', payload);
  return res.data;
}

export function useSubmitContact() {
  return useMutation({ mutationFn: submitContact });
}
