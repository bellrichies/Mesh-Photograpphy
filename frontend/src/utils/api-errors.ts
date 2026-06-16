import axios from 'axios';
import type { ApiErrorResponse } from '@/types/api';

function stringifyApiError(value: unknown): string | null {
  if (!value) {
    return null;
  }

  if (typeof value === 'string') {
    return value;
  }

  if (Array.isArray(value)) {
    for (const item of value) {
      const message = stringifyApiError(item);
      if (message) return message;
    }
    return null;
  }

  if (typeof value === 'object') {
    const record = value as Record<string, unknown>;
    if (typeof record.message === 'string') {
      return record.message;
    }
  }

  return null;
}

export function extractApiErrors(error: unknown): Record<string, string> {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as ApiErrorResponse | undefined;
    if (data?.errors && typeof data.errors === 'object' && Object.keys(data.errors).length > 0) {
      return Object.fromEntries(
        Object.entries(data.errors)
          .map(([field, value]) => [field, stringifyApiError(value)])
          .filter((entry): entry is [string, string] => typeof entry[1] === 'string')
      );
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
