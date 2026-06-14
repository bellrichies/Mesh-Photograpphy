import { useMutation } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';

export interface BookingPayload {
  name: string;
  email: string;
  event_type: string;
  phone?: string;
  event_date?: string;
  location?: string;
  notes?: string;
}

async function submitBooking(payload: BookingPayload): Promise<ApiResponse<null>> {
  const res = await apiClient.post<ApiResponse<null>>('/booking', payload);
  return res.data;
}

export function useSubmitBooking() {
  return useMutation({ mutationFn: submitBooking });
}
