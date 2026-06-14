export interface ApiResponse<T> {
  ok: boolean;
  message: string;
  data: T;
  errors: Record<string, string>;
  meta: Record<string, unknown>;
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
  };
}

export interface ApiErrorResponse {
  ok: false;
  message: string;
  data: null;
  errors: Record<string, string>;
  meta: { status: number };
}
