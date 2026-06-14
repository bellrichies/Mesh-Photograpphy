import { useState, useEffect, useRef } from 'react';
import { Check, X, Loader2 } from 'lucide-react';
import { checkSlugAvailability, type SlugCheckType } from '@/api/admin/slug';
import { cn } from '@/utils/cn';

interface Props {
  value: string;
  onChange: (val: string) => void;
  type: SlugCheckType;
  exceptId?: number;
  error?: string;
  disabled?: boolean;
}

export default function SlugInput({ value, onChange, type, exceptId, error, disabled }: Props) {
  const [checking, setChecking] = useState(false);
  const [available, setAvailable] = useState<boolean | null>(null);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (!value) { setAvailable(null); return; }

    if (timerRef.current) clearTimeout(timerRef.current);
    timerRef.current = setTimeout(async () => {
      setChecking(true);
      try {
        const result = await checkSlugAvailability(value, type, exceptId);
        setAvailable(result.available);
      } catch {
        setAvailable(null);
      } finally {
        setChecking(false);
      }
    }, 500);

    return () => { if (timerRef.current) clearTimeout(timerRef.current); };
  }, [value, type, exceptId]);

  return (
    <div className="relative">
      <input
        type="text"
        value={value}
        onChange={(e) => {
          setAvailable(null);
          onChange(e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-'));
        }}
        disabled={disabled}
        className={cn(
          'w-full px-3 py-2 pr-9 rounded-lg border text-sm font-body bg-white transition-colors',
          'focus:outline-none focus:ring-2 focus:ring-bronze focus:border-transparent',
          error ? 'border-red-400 bg-red-50' : 'border-cream hover:border-taupe',
          disabled && 'opacity-60 cursor-not-allowed'
        )}
        placeholder="my-slug-here"
      />
      <span className="absolute right-3 top-1/2 -translate-y-1/2">
        {checking && <Loader2 size={14} className="animate-spin text-taupe" />}
        {!checking && available === true  && <Check size={14} className="text-green-600" />}
        {!checking && available === false && <X    size={14} className="text-red-600" />}
      </span>
      {error && <p className="text-xs text-red-600 font-body mt-1" role="alert">{error}</p>}
      {!error && available === false && (
        <p className="text-xs text-red-600 font-body mt-1">This slug is already taken.</p>
      )}
    </div>
  );
}
