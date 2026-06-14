import type { ReactNode } from 'react';
import { cn } from '@/utils/cn';

interface Props {
  label: string;
  htmlFor?: string;
  error?: string;
  hint?: string;
  required?: boolean;
  className?: string;
  children: ReactNode;
}

export default function FormField({
  label,
  htmlFor,
  error,
  hint,
  required,
  className,
  children,
}: Props) {
  return (
    <div className={cn('space-y-1', className)}>
      <label
        htmlFor={htmlFor}
        className="block text-sm font-medium text-charcoal font-body"
      >
        {label}
        {required && <span className="text-red-500 ml-0.5">*</span>}
      </label>
      {children}
      {error && (
        <p className="text-xs text-red-600 font-body" role="alert">
          {error}
        </p>
      )}
      {hint && !error && (
        <p className="text-xs text-taupe font-body">{hint}</p>
      )}
    </div>
  );
}

export function fieldClass(hasError: boolean): string {
  return cn(
    'w-full px-3 py-2 rounded-lg border text-sm font-body bg-white transition-colors',
    'focus:outline-none focus:ring-2 focus:ring-bronze focus:border-transparent',
    hasError
      ? 'border-red-400 bg-red-50'
      : 'border-cream hover:border-taupe'
  );
}
