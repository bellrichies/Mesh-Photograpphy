import { useState, useCallback } from 'react';
import { Search, Upload, Trash2, Info } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAdminMedia, useUploadMedia, useDeleteMedia, useUpdateMedia } from '@/api/admin/media';
import PageHeader from '@/components/admin/PageHeader';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import Pagination from '@/components/admin/Pagination';
import { getErrorMessage } from '@/utils/api-errors';
import type { MediaRecord } from '@/types/models';
import { cn } from '@/utils/cn';

function MediaDetail({
  media, onClose, onDelete, onSaveAlt,
}: {
  media: MediaRecord;
  onClose: () => void;
  onDelete: () => void;
  onSaveAlt: (alt: string) => void;
}) {
  const [alt, setAlt] = useState(media.alt_text ?? '');

  return (
    <aside className="w-64 flex-shrink-0 bg-white border border-cream rounded-xl p-4 space-y-4 self-start">
      <img
        src={media.thumb_url ?? media.url}
        alt={media.alt_text ?? ''}
        className="w-full aspect-square object-cover rounded-lg"
        width={224}
        height={224}
      />
      <div>
        <p className="text-xs text-taupe font-body truncate" title={media.original_name}>
          {media.original_name}
        </p>
        <p className="text-xs text-taupe font-body">
          {(media.size_bytes / 1024).toFixed(0)} KB
          {media.width ? ` · ${media.width}×${media.height}` : ''}
        </p>
      </div>
      <div>
        <label className="block text-xs font-medium text-charcoal font-body mb-1">Alt Text</label>
        <textarea
          value={alt}
          onChange={(e) => setAlt(e.target.value)}
          rows={2}
          className="w-full px-2 py-1.5 border border-cream rounded text-xs font-body focus:outline-none focus:ring-1 focus:ring-bronze"
        />
        <button
          onClick={() => onSaveAlt(alt)}
          className="mt-1 w-full py-1.5 text-xs font-body bg-ivory-warm hover:bg-cream rounded transition-colors text-charcoal"
        >
          Save Alt Text
        </button>
      </div>
      <div className="flex gap-2">
        <button
          onClick={onDelete}
          className="flex-1 py-1.5 text-xs font-body bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors"
        >
          Delete
        </button>
        <button
          onClick={onClose}
          className="flex-1 py-1.5 text-xs font-body border border-cream hover:bg-ivory-warm rounded transition-colors text-charcoal"
        >
          Close
        </button>
      </div>
    </aside>
  );
}

export default function MediaPage() {
  const [page, setPage]         = useState(1);
  const [q, setQ]               = useState('');
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [selected, setSelected] = useState<MediaRecord | null>(null);

  const { data, isLoading } = useAdminMedia({ page, q, per_page: 24 });
  const uploadMutation      = useUploadMedia();
  const deleteMutation      = useDeleteMedia();
  const updateMutation      = useUpdateMedia();

  const handleUpload = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files ?? []);
    for (const file of files) {
      try {
        await uploadMutation.mutateAsync(file);
      } catch (err) {
        toast.error(`Failed to upload ${file.name}: ${getErrorMessage(err)}`);
      }
    }
    if (files.length) toast.success(`${files.length} file(s) uploaded.`);
    e.target.value = '';
  }, [uploadMutation]);

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync(deleteId);
      toast.success('Media deleted.');
      if (selected?.id === deleteId) setSelected(null);
    } catch (err) {
      toast.error(getErrorMessage(err));
    } finally {
      setDeleteId(null);
    }
  };

  const handleAltSave = async (id: number, alt_text: string) => {
    try {
      await updateMutation.mutateAsync({ id, alt_text });
      toast.success('Alt text saved.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  return (
    <div>
      <PageHeader
        title="Media Library"
        subtitle="Upload and manage images."
        actions={
          <label
            className={cn(
              'flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg cursor-pointer',
              'hover:bg-bronze-light transition-colors',
              uploadMutation.isPending && 'opacity-60 cursor-not-allowed'
            )}
          >
            <Upload size={16} />
            {uploadMutation.isPending ? 'Uploading…' : 'Upload'}
            <input
              type="file"
              accept="image/*"
              multiple
              className="sr-only"
              disabled={uploadMutation.isPending}
              onChange={handleUpload}
            />
          </label>
        }
      />

      <div className="mb-4 relative max-w-xs">
        <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-taupe" />
        <input
          type="text"
          value={q}
          onChange={(e) => { setQ(e.target.value); setPage(1); }}
          placeholder="Search media…"
          className="w-full pl-9 pr-3 py-2 border border-cream rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-bronze"
        />
      </div>

      <div className="flex gap-6">
        <div className="flex-1 min-w-0">
          {isLoading ? (
            <div className="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
              {Array.from({ length: 12 }).map((_, i) => (
                <div key={i} className="aspect-square bg-cream rounded-lg animate-pulse" />
              ))}
            </div>
          ) : data?.data.length === 0 ? (
            <div className="bg-white rounded-xl border border-cream py-16 text-center">
              <p className="text-taupe font-body text-sm">No media files yet. Upload some images to get started.</p>
            </div>
          ) : (
            <div className="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
              {data?.data.map((m) => (
                <div
                  key={m.id}
                  role="button"
                  tabIndex={0}
                  onClick={() => setSelected(m)}
                  onKeyDown={(e) => e.key === 'Enter' && setSelected(m)}
                  className={cn(
                    'relative aspect-square rounded-lg overflow-hidden border-2 group transition-all cursor-pointer',
                    selected?.id === m.id ? 'border-bronze' : 'border-transparent hover:border-bronze/40'
                  )}
                >
                  <img
                    src={m.thumb_url ?? m.url}
                    alt={m.alt_text ?? ''}
                    className="w-full h-full object-cover"
                    width={150}
                    height={150}
                    loading="lazy"
                  />
                  <div className="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5">
                    <span className="p-1 bg-white rounded text-charcoal">
                      <Info size={12} />
                    </span>
                    <button
                      type="button"
                      onClick={(e) => { e.stopPropagation(); setDeleteId(m.id); }}
                      className="p-1 bg-red-600 rounded text-white"
                      aria-label="Delete"
                    >
                      <Trash2 size={12} />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}

          {data && (
            <Pagination
              currentPage={data.meta.current_page}
              lastPage={data.meta.last_page}
              total={data.meta.total}
              perPage={data.meta.per_page}
              onPageChange={setPage}
            />
          )}
        </div>

        {selected && (
          <MediaDetail
            media={selected}
            onClose={() => setSelected(null)}
            onDelete={() => { setDeleteId(selected.id); setSelected(null); }}
            onSaveAlt={(alt) => handleAltSave(selected.id, alt)}
          />
        )}
      </div>

      <ConfirmDialog
        open={deleteId !== null}
        title="Delete media?"
        message="This will remove the file record. Any content referencing this image may break."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
