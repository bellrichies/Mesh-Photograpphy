import { useState } from 'react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminPages,
  useCreatePage,
  useUpdatePage,
  useDeletePage,
  type AdminPage,
  type AdminPagePayload,
} from '@/api/admin/pages';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import FormField, { fieldClass } from '@/components/admin/FormField';
import SlugInput from '@/components/admin/SlugInput';
import { getErrorMessage } from '@/utils/api-errors';

const schema = z.object({
  title:           z.string().min(1, 'Title is required'),
  slug:            z.string().min(1, 'Slug is required'),
  template:        z.string().optional(),
  is_published:    z.enum(['true', 'false']),
  seo_title:       z.string().optional(),
  seo_description: z.string().optional(),
});

type FormValues = z.infer<typeof schema>;

function PageFormModal({ page, onClose }: { page?: AdminPage; onClose: () => void }) {
  const isEdit = !!page;
  const createMut = useCreatePage();
  const updateMut = useUpdatePage(page?.id ?? 0);

  const { register, handleSubmit, control, setValue, watch, formState: { errors, isSubmitting } } =
    useForm<FormValues>({
      resolver: zodResolver(schema),
      defaultValues: {
        title:           page?.title                    ?? '',
        slug:            page?.slug                     ?? '',
        template:        page?.template                 ?? '',
        is_published:    (page?.status === 'published' ? 'true' : 'false') as 'true' | 'false',
        seo_title:       page?.seo?.meta_title          ?? '',
        seo_description: page?.seo?.meta_description    ?? '',
      },
    });

  const title = watch('title');
  const slug  = watch('slug');

  const handleTitleBlur = () => {
    if (!isEdit && !slug && title) {
      setValue('slug', title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
    }
  };

  const onSubmit = async (values: FormValues) => {
    const payload: AdminPagePayload = {
      title:           values.title,
      slug:            values.slug,
      template:        values.template || null,
      is_published:    values.is_published === 'true',
      seo_title:       values.seo_title       || null,
      seo_description: values.seo_description || null,
    };
    try {
      if (isEdit) { await updateMut.mutateAsync(payload); toast.success('Page saved.'); }
      else        { await createMut.mutateAsync(payload); toast.success('Page created.'); }
      onClose();
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'New'} Page</h2>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <FormField label="Title" htmlFor="title" error={errors.title?.message} required>
            <input
              id="title"
              type="text"
              className={fieldClass(!!errors.title)}
              {...register('title', { onBlur: handleTitleBlur })}
            />
          </FormField>

          <FormField label="Slug" htmlFor="slug" error={errors.slug?.message} required>
            <Controller
              name="slug"
              control={control}
              render={({ field }) => (
                <SlugInput
                  value={field.value}
                  onChange={field.onChange}
                  type="page"
                  exceptId={isEdit ? page?.id : undefined}
                />
              )}
            />
          </FormField>

          <FormField label="Template" htmlFor="template">
            <input id="template" type="text" className={fieldClass(false)} placeholder="default" {...register('template')} />
          </FormField>

          <FormField label="Status" htmlFor="is_published">
            <select id="is_published" className={fieldClass(false)} {...register('is_published')}>
              <option value="true">Published</option>
              <option value="false">Draft</option>
            </select>
          </FormField>

          <fieldset className="border border-cream rounded-lg p-4">
            <legend className="text-xs font-medium text-taupe font-body uppercase tracking-wider px-1">SEO</legend>
            <div className="space-y-3">
              <FormField label="SEO Title" htmlFor="seo_title">
                <input id="seo_title" type="text" className={fieldClass(false)} {...register('seo_title')} />
              </FormField>
              <FormField label="SEO Description" htmlFor="seo_description">
                <textarea id="seo_description" rows={2} className={fieldClass(false)} {...register('seo_description')} />
              </FormField>
            </div>
          </fieldset>

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

export default function PagesPage() {
  const [editing, setEditing]   = useState<AdminPage | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminPages();
  const deleteMutation      = useDeletePage();

  const handleDelete = async () => {
    if (!deleteId) return;
    try { await deleteMutation.mutateAsync(deleteId); toast.success('Page deleted.'); }
    catch (err) { toast.error(getErrorMessage(err)); }
    finally { setDeleteId(null); }
  };

  const columns: Column<AdminPage>[] = [
    {
      key: 'title',
      header: 'Page',
      render: (p) => (
        <div>
          <div className="font-medium text-charcoal">{p.title}</div>
          <div className="text-xs text-taupe font-mono">/{p.slug}</div>
        </div>
      ),
    },
    { key: 'template', header: 'Template', render: (p) => p.template ?? <span className="text-taupe text-xs">default</span> },
    { key: 'status',   header: 'Status',   render: (p) => <StatusBadge status={p.status} /> },
    {
      key: 'actions', header: '',
      render: (p) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => { setEditing(p); setShowForm(true); }} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(p.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Pages"
        subtitle="Manage CMS pages."
        actions={
          <button onClick={() => { setEditing(undefined); setShowForm(true); }} className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light">
            <Plus size={16} /> New Page
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(p) => p.id} loading={isLoading} emptyMessage="No pages found." />
      {showForm && (
        <PageFormModal page={editing} onClose={() => { setShowForm(false); setEditing(undefined); }} />
      )}
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete page?"
        message="This page will be permanently deleted."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
