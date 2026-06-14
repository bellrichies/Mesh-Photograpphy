import axios from 'axios';
import type { ApiErrorResponse } from '@/types/api';

export function extractApiErrors(error: unknown): Record<string, string> {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as ApiErrorResponse | undefined;
    if (data?.errors && typeof data.errors === 'object' && Object.keys(data.errors).length > 0) {
      return data.errors;
    }
    if (data?.message) {
      return { _root: data.message };
    }
  }
  return { _root: 'An unexpected error occurred' };
}

export function getErrorMessage(error: unknown): string {
  const errors = extractApiErrors(error);
  return errors._root ?? Object.values(errors)[0] ?? 'Something went wrong';
}
