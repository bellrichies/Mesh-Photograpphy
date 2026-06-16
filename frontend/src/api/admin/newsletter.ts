import { useQuery } from '@tanstack/react-query';
import { apiClient } from '@/api/client';
import type { PaginatedResponse } from '@/types/api';

export interface NewsletterSubscriber {
  id: number;
  email: string;
  status: 'active' | 'unsubscribed';
  source: string;
  ip_address: string | null;
  user_agent: string | null;
  subscribed_at: string;
  created_at: string;
  updated_at: string;
}

export interface NewsletterSubscriberFilters {
  page?: number;
  per_page?: number;
  status?: 'active' | 'unsubscribed' | '';
  q?: string;
}

export const adminNewsletterKeys = {
  all: () => ['admin', 'newsletter'] as const,
  subscribers: (filters: NewsletterSubscriberFilters) => [...adminNewsletterKeys.all(), 'subscribers', filters] as const,
};

export function useNewsletterSubscribers(filters: NewsletterSubscriberFilters = {}) {
  return useQuery({
    queryKey: adminNewsletterKeys.subscribers(filters),
    queryFn: async () => {
      const res = await apiClient.get<PaginatedResponse<NewsletterSubscriber>>(
        '/admin/newsletter/subscriptions',
        { params: filters }
      );
      return res.data;
    },
    staleTime: 30_000,
  });
}

export async function downloadNewsletterSubscribersCsv(filters: NewsletterSubscriberFilters = {}): Promise<void> {
  const res = await apiClient.get<Blob>('/admin/newsletter/subscriptions/export', {
    params: filters,
    responseType: 'blob',
  });

  const url = URL.createObjectURL(res.data);
  const link = document.createElement('a');
  link.href = url;
  link.download = `newsletter-subscribers-${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}
