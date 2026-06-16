import { useState } from 'react';
import { Plus, Pencil, Trash2, Star } from 'lucide-react';
import toast from 'react-hot-toast';
import { Controller, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminTestimonials,
  useCreateTestimonial,
  useUpdateTestimonial,
  useDeleteTestimonial,
  type AdminTestimonial,
  type AdminTestimonialPayload,
} from '@/api/admin/testimonials';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import FormField, { fieldClass } from '@/components/admin/FormField';
import MediaPicker from '@/components/admin/MediaPicker';
import { getErrorMessage } from '@/utils/api-errors';
import type { MediaRecord } from '@/types/models';

const nullableNumber = z.preprocess(
  (value) => (value === '' || value === undefined ? null : value),
  z.coerce.number().int().nullable()
);

const schema = z.object({
  client_name: z.string().min(1, 'Name is required'),
  client_role: z.string().optional(),
  body:        z.string().min(1, 'Body is required'),
  rating:      z.coerce.number().min(1).max(5),
  portrait_id: nullableNumber.optional(),
  portrait:    z.custom<MediaRecord | null>().nullable().optional(),
  status:      z.enum(['draft', 'published']),
  sort_order:  z.coerce.number().int().optional(),
});

type FormValues = z.infer<typeof schema>;

function TestimonialModal({
  testimonial, onClose,
}: { testimonial?: AdminTestimonial; onClose: () => void }) {
  const isEdit = !!testimonial;
  const createMutation = useCreateTestimonial();
  const updateMutation = useUpdateTestimonial(testimonial?.id ?? 0);

  const { register, handleSubmit, control, setValue, formState: { errors, isSubmitting } } =
    useForm<FormValues>({
      resolver: zodResolver(schema),
      defaultValues: {
        client_name: testimonial?.client_name ?? '',
        client_role: testimonial?.client_role ?? '',
        body:        testimonial?.body ?? '',
        rating:      testimonial?.rating ?? 5,
        portrait_id: testimonial?.portrait_id ?? null,
        portrait:    testimonial?.portrait ?? null,
        status:      testimonial?.status ?? 'draft',
        sort_order:  testimonial?.sort_order ?? 0,
      },
    });

  const onSubmit = async (values: FormValues) => {
    const payload: AdminTestimonialPayload = {
      client_name: values.client_name,
      client_role: values.client_role || null,
      body:        values.body,
      rating:      values.rating,
      portrait_id: values.portrait_id ?? values.portrait?.id ?? null,
      status:      values.status,
      sort_order:  values.sort_order ?? 0,
    };
    try {
      if (isEdit) { await updateMutation.mutateAsync(payload); toast.success('Testimonial saved.'); }
      else        { await createMutation.mutateAsync(payload); toast.success('Testimonial added.'); }
      onClose();
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-lg p-6 max-h-[92vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'Add'} Testimonial</h2>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <FormField label="Client Name" htmlFor="client_name" error={errors.client_name?.message} required>
            <input id="client_name" type="text" className={fieldClass(!!errors.client_name)} {...register('client_name')} />
          </FormField>
          <FormField label="Role / Title" htmlFor="client_role">
            <input id="client_role" type="text" className={fieldClass(false)} {...register('client_role')} />
          </FormField>
          <FormField label="Testimonial" htmlFor="body" error={errors.body?.message} required>
            <textarea id="body" rows={4} className={fieldClass(!!errors.body)} {...register('body')} />
          </FormField>
          <FormField
            label="Recipient Image"
            hint="Optional portrait displayed with this testimonial."
          >
            <Controller
              name="portrait"
              control={control}
              render={({ field }) => (
                <MediaPicker
                  value={field.value ?? null}
                  onChange={(media) => {
                    field.onChange(media);
                    setValue('portrait_id', media?.id ?? null, { shouldDirty: true });
                  }}
                  label="Select recipient image"
                />
              )}
            />
          </FormField>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <FormField label="Rating (1-5)" htmlFor="rating">
              <input id="rating" type="number" min={1} max={5} className={fieldClass(false)} {...register('rating')} />
            </FormField>
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
              {isSubmitting ? 'Saving...' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function TestimonialsPage() {
  const [editing, setEditing]   = useState<AdminTestimonial | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminTestimonials();
  const deleteMutation      = useDeleteTestimonial();

  const handleDelete = async () => {
    if (!deleteId) return;
    try { await deleteMutation.mutateAsync(deleteId); toast.success('Testimonial deleted.'); }
    catch (err) { toast.error(getErrorMessage(err)); }
    finally { setDeleteId(null); }
  };

  const columns: Column<AdminTestimonial>[] = [
    {
      key: 'client',
      header: 'Client',
      render: (t) => (
        <div className="flex items-center gap-3">
          {t.portrait && (
            <img
              src={t.portrait.thumb_url ?? t.portrait.url}
              alt={t.portrait.alt_text ?? t.client_name}
              className="h-10 w-10 rounded-full object-cover border border-cream"
              width={40}
              height={40}
              loading="lazy"
            />
          )}
          <div>
            <div className="font-medium text-charcoal">{t.client_name}</div>
            {t.client_role && <div className="text-xs text-taupe">{t.client_role}</div>}
          </div>
        </div>
      ),
    },
    {
      key: 'rating',
      header: 'Rating',
      render: (t) => (
        <span className="flex items-center gap-0.5 text-gold">
          {Array.from({ length: t.rating }).map((_, i) => <Star key={i} size={12} fill="currentColor" />)}
        </span>
      ),
    },
    { key: 'status',     header: 'Status',     render: (t) => <StatusBadge status={t.status} /> },
    { key: 'sort_order', header: 'Order',       render: (t) => t.sort_order, className: 'w-20 text-center' },
    {
      key: 'actions', header: '',
      render: (t) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => { setEditing(t); setShowForm(true); }} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(t.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Testimonials"
        subtitle="Client reviews shown on the homepage."
        actions={
          <button onClick={() => { setEditing(undefined); setShowForm(true); }} className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light">
            <Plus size={16} /> Add Testimonial
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(t) => t.id} loading={isLoading} emptyMessage="No testimonials yet." />
      {showForm && (
        <TestimonialModal
          testimonial={editing}
          onClose={() => { setShowForm(false); setEditing(undefined); }}
        />
      )}
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete testimonial?"
        message="This will remove the testimonial permanently."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
