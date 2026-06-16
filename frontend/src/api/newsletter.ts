import { useMutation } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';

export interface NewsletterSubscribePayload {
  email: string;
}

export interface NewsletterSubscribeResult {
  status: 'subscribed' | 'already_subscribed' | 'reactivated';
}

async function subscribeNewsletter(
  payload: NewsletterSubscribePayload
): Promise<ApiResponse<NewsletterSubscribeResult>> {
  const res = await apiClient.post<ApiResponse<NewsletterSubscribeResult>>(
    '/newsletter/subscribe',
    payload
  );
  return res.data;
}

export function useSubscribeNewsletter() {
  return useMutation({ mutationFn: subscribeNewsletter });
}
