import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';

export type SlugCheckType = 'gallery' | 'blog_post' | 'service' | 'page';

export async function checkSlugAvailability(
  slug: string,
  type: SlugCheckType,
  except?: number
): Promise<{ slug: string; available: boolean }> {
  const res = await apiClient.get<ApiResponse<{ slug: string; available: boolean }>>(
    '/admin/slug-check',
    { params: { slug, type, except } }
  );
  return res.data.data;
}
