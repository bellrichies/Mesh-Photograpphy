import { useState, useCallback } from 'react';
import { X, Upload, Search, Check } from 'lucide-react';
import { useAdminMedia } from '@/api/admin/media';
import { useUploadMedia } from '@/api/admin/media';
import type { MediaRecord } from '@/types/models';
import { cn } from '@/utils/cn';

interface Props {
  value: MediaRecord | null;
  onChange: (media: MediaRecord | null) => void;
  label?: string;
}

export default function MediaPicker({ value, onChange, label = 'Select Image' }: Props) {
  const [open, setOpen]   = useState(false);
  const [q, setQ]         = useState('');
  const [page, setPage]   = useState(1);

  const { data, isLoading } = useAdminMedia({ q, per_page: 24, page, type: 'image' });
  const upload = useUploadMedia();

  const handleSelect = (media: MediaRecord) => {
    onChange(media);
    setOpen(false);
  };

  const handleRemove = () => onChange(null);

  const handleFileDrop = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const media = await upload.mutateAsync(file);
    onChange(media);
    setOpen(false);
  }, [upload, onChange]);

  return (
    <>
      <div className="border border-cream rounded-lg overflow-hidden">
        {value ? (
          <div className="relative group">
            <img
              src={value.thumb_url ?? value.url}
              alt={value.alt_text ?? ''}
              className="w-full h-40 object-cover"
              width={400}
              height={160}
            />
            <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
              <button
                type="button"
                onClick={() => setOpen(true)}
                className="px-3 py-1.5 bg-white text-charcoal text-xs font-body rounded-lg"
              >
                Change
              </button>
              <button
                type="button"
                onClick={handleRemove}
                className="px-3 py-1.5 bg-red-600 text-white text-xs font-body rounded-lg"
              >
                Remove
              </button>
            </div>
          </div>
        ) : (
          <button
            type="button"
            onClick={() => setOpen(true)}
            className="w-full h-32 flex flex-col items-center justify-center gap-2 text-taupe hover:text-bronze hover:bg-ivory-warm transition-colors"
          >
            <Upload size={20} />
            <span className="text-sm font-body">{label}</span>
          </button>
        )}
      </div>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/50" onClick={() => setOpen(false)} />
          <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-3xl max-h-[85vh] flex flex-col">
            <div className="flex items-center justify-between p-4 border-b border-cream">
              <h2 className="font-display text-xl text-charcoal">Media Library</h2>
              <button onClick={() => setOpen(false)} aria-label="Close">
                <X size={20} className="text-taupe hover:text-charcoal" />
              </button>
            </div>

            <div className="p-4 border-b border-cream flex items-center gap-3">
              <div className="relative flex-1">
                <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-taupe" />
                <input
                  type="text"
                  value={q}
                  onChange={(e) => { setQ(e.target.value); setPage(1); }}
                  placeholder="Search media…"
                  className="w-full pl-9 pr-3 py-2 border border-cream rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-bronze"
                />
              </div>
              <label className={cn(
                'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-body cursor-pointer transition-colors',
                'bg-bronze text-ivory hover:bg-bronze-light',
                upload.isPending && 'opacity-60 cursor-not-allowed'
              )}>
                <Upload size={14} />
                {upload.isPending ? 'Uploading…' : 'Upload'}
                <input
                  type="file"
                  accept="image/*"
                  className="sr-only"
                  disabled={upload.isPending}
                  onChange={handleFileDrop}
                />
              </label>
            </div>

            <div className="flex-1 overflow-y-auto p-4">
              {isLoading ? (
                <div className="grid grid-cols-4 gap-3">
                  {Array.from({ length: 8 }).map((_, i) => (
                    <div key={i} className="aspect-square bg-cream rounded-lg animate-pulse" />
                  ))}
                </div>
              ) : data?.data.length === 0 ? (
                <p className="text-center text-taupe py-12 font-body text-sm">No media found.</p>
              ) : (
                <div className="grid grid-cols-4 gap-3">
                  {data?.data.map((media) => (
                    <button
                      key={media.id}
                      type="button"
                      onClick={() => handleSelect(media)}
                      className={cn(
                        'relative aspect-square rounded-lg overflow-hidden border-2 transition-all',
                        value?.id === media.id ? 'border-bronze' : 'border-transparent hover:border-bronze/50'
                      )}
                    >
                      <img
                        src={media.thumb_url ?? media.url}
                        alt={media.alt_text ?? ''}
                        className="w-full h-full object-cover"
                        width={200}
                        height={200}
                        loading="lazy"
                      />
                      {value?.id === media.id && (
                        <div className="absolute inset-0 bg-bronze/20 flex items-center justify-center">
                          <Check size={20} className="text-bronze" />
                        </div>
                      )}
                    </button>
                  ))}
                </div>
              )}
            </div>

            {data && data.meta.last_page > 1 && (
              <div className="p-4 border-t border-cream flex items-center justify-between">
                <button
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  disabled={page === 1}
                  className="text-sm font-body text-bronze disabled:opacity-40"
                >
                  Previous
                </button>
                <span className="text-sm text-taupe font-body">
                  Page {page} of {data.meta.last_page}
                </span>
                <button
                  onClick={() => setPage((p) => Math.min(data.meta.last_page, p + 1))}
                  disabled={page === data.meta.last_page}
                  className="text-sm font-body text-bronze disabled:opacity-40"
                >
                  Next
                </button>
              </div>
            )}
          </div>
        </div>
      )}
    </>
  );
}
