import { useState } from 'react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminHeroSlides,
  useCreateHeroSlide,
  useUpdateHeroSlide,
  useDeleteHeroSlide,
  type AdminHeroSlide,
  type AdminHeroSlidePayload,
} from '@/api/admin/hero-slides';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import FormField, { fieldClass } from '@/components/admin/FormField';
import MediaPicker from '@/components/admin/MediaPicker';
import { getErrorMessage } from '@/utils/api-errors';
import type { MediaRecord } from '@/types/models';

const schema = z.object({
  title:      z.string().min(1, 'Title is required'),
  subtitle:   z.string().optional(),
  cta_label:  z.string().optional(),
  cta_url:    z.string().optional(),
  sort_order: z.coerce.number().int().optional(),
  status:     z.enum(['draft', 'published']),
});

type FormValues = z.infer<typeof schema>;

function SlideModal({ slide, onClose }: { slide?: AdminHeroSlide; onClose: () => void }) {
  const isEdit       = !!slide;
  const createMut    = useCreateHeroSlide();
  const updateMut    = useUpdateHeroSlide(slide?.id ?? 0);
  const [bg, setBg]  = useState<MediaRecord | null>(slide?.background_image ?? null);

  const { register, handleSubmit, formState: { errors, isSubmitting } } =
    useForm<FormValues>({
      resolver: zodResolver(schema),
      defaultValues: {
        title:      slide?.title      ?? '',
        subtitle:   slide?.subtitle   ?? '',
        cta_label:  slide?.cta_label  ?? '',
        cta_url:    slide?.cta_url    ?? '',
        sort_order: slide?.sort_order ?? 0,
        status:     slide?.status     ?? 'draft',
      },
    });

  const onSubmit = async (values: FormValues) => {
    const payload: AdminHeroSlidePayload = {
      title:               values.title,
      subtitle:            values.subtitle    || null,
      cta_label:           values.cta_label   || null,
      cta_url:             values.cta_url     || null,
      sort_order:          values.sort_order  ?? 0,
      status:              values.status,
      background_image_id: bg?.id ?? null,
    };
    try {
      if (isEdit) { await updateMut.mutateAsync(payload); toast.success('Slide saved.'); }
      else        { await createMut.mutateAsync(payload); toast.success('Slide created.'); }
      onClose();
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'New'} Hero Slide</h2>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <FormField label="Title" htmlFor="title" error={errors.title?.message} required>
            <input id="title" type="text" className={fieldClass(!!errors.title)} {...register('title')} />
          </FormField>
          <FormField label="Subtitle" htmlFor="subtitle">
            <input id="subtitle" type="text" className={fieldClass(false)} {...register('subtitle')} />
          </FormField>
          <FormField label="Background Image">
            <MediaPicker value={bg} onChange={setBg} label="Select background image" />
          </FormField>
          <div className="grid grid-cols-2 gap-4">
            <FormField label="CTA Label" htmlFor="cta_label">
              <input id="cta_label" type="text" className={fieldClass(false)} {...register('cta_label')} />
            </FormField>
            <FormField label="CTA URL" htmlFor="cta_url">
              <input id="cta_url" type="text" className={fieldClass(false)} {...register('cta_url')} />
            </FormField>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <FormField label="Sort Order" htmlFor="sort_order">
              <input id="sort_order" type="number" min={0} className={fieldClass(false)} {...register('sort_order')} />
            </FormField>
            <FormField label="Status" htmlFor="status">
              <select id="status" className={fieldClass(false)} {...register('status')}>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </FormField>
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-body text-charcoal border border-cream rounded-lg hover:bg-ivory-warm">Cancel</button>
            <button type="submit" disabled={isSubmitting} className="px-4 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60">
              {isSubmitting ? 'Saving…' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function HeroSlidesPage() {
  const [editing, setEditing]   = useState<AdminHeroSlide | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminHeroSlides();
  const deleteMutation      = useDeleteHeroSlide();

  const handleDelete = async () => {
    if (!deleteId) return;
    try { await deleteMutation.mutateAsync(deleteId); toast.success('Slide deleted.'); }
    catch (err) { toast.error(getErrorMessage(err)); }
    finally { setDeleteId(null); }
  };

  const columns: Column<AdminHeroSlide>[] = [
    {
      key: 'title',
      header: 'Slide',
      render: (s) => (
        <div className="flex items-center gap-3">
          {s.background_image?.thumb_url && (
            <img src={s.background_image.thumb_url} alt="" className="w-14 h-10 rounded object-cover flex-shrink-0" width={56} height={40} />
          )}
          <div>
            <div className="font-medium text-charcoal">{s.title}</div>
            {s.subtitle && <div className="text-xs text-taupe">{s.subtitle}</div>}
          </div>
        </div>
      ),
    },
    { key: 'cta',    header: 'CTA',    render: (s) => s.cta_label ?? <span className="text-taupe text-xs">—</span> },
    { key: 'order',  header: 'Order',  render: (s) => s.sort_order, className: 'w-20 text-center' },
    { key: 'status', header: 'Status', render: (s) => <StatusBadge status={s.status} /> },
    {
      key: 'actions', header: '',
      render: (s) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => { setEditing(s); setShowForm(true); }} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(s.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Hero Slides"
        subtitle="Manage the homepage hero carousel."
        actions={
          <button onClick={() => { setEditing(undefined); setShowForm(true); }} className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light">
            <Plus size={16} /> New Slide
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(s) => s.id} loading={isLoading} emptyMessage="No hero slides yet." />
      {showForm && (
        <SlideModal slide={editing} onClose={() => { setShowForm(false); setEditing(undefined); }} />
      )}
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete slide?"
        message="This slide will be removed from the hero carousel."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
