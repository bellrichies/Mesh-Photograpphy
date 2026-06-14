import { useEffect, useRef } from 'react';
import { AlertTriangle, X } from 'lucide-react';
import { cn } from '@/utils/cn';

interface Props {
  open: boolean;
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  onConfirm: () => void;
  onCancel: () => void;
  loading?: boolean;
  variant?: 'danger' | 'warning';
}

export default function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel = 'Delete',
  cancelLabel  = 'Cancel',
  onConfirm,
  onCancel,
  loading = false,
  variant = 'danger',
}: Props) {
  const cancelRef = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (open) cancelRef.current?.focus();
  }, [open]);

  useEffect(() => {
    const handle = (e: KeyboardEvent) => { if (e.key === 'Escape') onCancel(); };
    if (open) document.addEventListener('keydown', handle);
    return () => document.removeEventListener('keydown', handle);
  }, [open, onCancel]);

  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="confirm-dialog-title"
    >
      <div
        className="absolute inset-0 bg-black/40"
        onClick={onCancel}
        aria-hidden="true"
      />
      <div className="relative bg-white rounded-2xl shadow-soft max-w-sm w-full p-6">
        <button
          onClick={onCancel}
          className="absolute top-4 right-4 text-taupe hover:text-charcoal"
          aria-label="Close"
        >
          <X size={18} />
        </button>

        <div className="flex items-start gap-4">
          <span
            className={cn(
              'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
              variant === 'danger' ? 'bg-red-100' : 'bg-yellow-100'
            )}
          >
            <AlertTriangle
              size={20}
              className={variant === 'danger' ? 'text-red-600' : 'text-yellow-600'}
            />
          </span>
          <div>
            <h3 id="confirm-dialog-title" className="font-body text-base font-semibold text-charcoal mb-1">
              {title}
            </h3>
            <p className="font-body text-sm text-taupe">{message}</p>
          </div>
        </div>

        <div className="flex justify-end gap-3 mt-6">
          <button
            ref={cancelRef}
            onClick={onCancel}
            disabled={loading}
            className="px-4 py-2 text-sm font-body text-charcoal border border-cream rounded-lg hover:bg-ivory transition-colors"
          >
            {cancelLabel}
          </button>
          <button
            onClick={onConfirm}
            disabled={loading}
            className={cn(
              'px-4 py-2 text-sm font-body text-white rounded-lg transition-colors',
              variant === 'danger'
                ? 'bg-red-600 hover:bg-red-700 disabled:opacity-60'
                : 'bg-yellow-600 hover:bg-yellow-700 disabled:opacity-60'
            )}
          >
            {loading ? 'Processing…' : confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
