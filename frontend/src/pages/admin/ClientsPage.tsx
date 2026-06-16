import { useState } from 'react';
import { Plus, Pencil, Trash2, Building2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { Controller, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminClients,
  useCreateClient,
  useUpdateClient,
  useDeleteClient,
  type AdminClient,
  type AdminClientPayload,
} from '@/api/admin/clients';
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
  name:        z.string().min(1, 'Name is required'),
  website_url: z.string().url('Enter a valid URL').or(z.literal('')).optional(),
  logo_id:     nullableNumber.optional(),
  logo:        z.custom<MediaRecord | null>().nullable().optional(),
  status:      z.enum(['draft', 'published']),
  sort_order:  z.coerce.number().int().optional(),
});

type FormValues = z.infer<typeof schema>;

function ClientModal({
  client, onClose,
}: { client?: AdminClient; onClose: () => void }) {
  const isEdit = !!client;
  const createMutation = useCreateClient();
  const updateMutation = useUpdateClient(client?.id ?? 0);

  const { register, handleSubmit, control, setValue, formState: { errors, isSubmitting } } =
    useForm<FormValues>({
      resolver: zodResolver(schema),
      defaultValues: {
        name:        client?.name ?? '',
        website_url: client?.website_url ?? '',
        logo_id:     client?.logo_id ?? null,
        logo:        client?.logo ?? null,
        status:      client?.status ?? 'published',
        sort_order:  client?.sort_order ?? 0,
      },
    });

  const onSubmit = async (values: FormValues) => {
    const payload: AdminClientPayload = {
      name:        values.name,
      website_url: values.website_url || null,
      logo_id:     values.logo_id ?? values.logo?.id ?? null,
      status:      values.status,
      sort_order:  values.sort_order ?? 0,
    };
    try {
      if (isEdit) { await updateMutation.mutateAsync(payload); toast.success('Client saved.'); }
      else        { await createMutation.mutateAsync(payload); toast.success('Client added.'); }
      onClose();
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-lg p-6 max-h-[92vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'Add'} Client</h2>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <FormField label="Client Name" htmlFor="name" error={errors.name?.message} required>
            <input id="name" type="text" className={fieldClass(!!errors.name)} {...register('name')} />
          </FormField>
          <FormField label="Website URL" htmlFor="website_url" error={errors.website_url?.message}>
            <input id="website_url" type="url" placeholder="https://…" className={fieldClass(!!errors.website_url)} {...register('website_url')} />
          </FormField>
          <FormField label="Logo" hint="Optional logo shown in the clients wall.">
            <Controller
              name="logo"
              control={control}
              render={({ field }) => (
                <MediaPicker
                  value={field.value ?? null}
                  onChange={(media) => {
                    field.onChange(media);
                    setValue('logo_id', media?.id ?? null, { shouldDirty: true });
                  }}
                  label="Select logo"
                />
              )}
            />
          </FormField>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

export default function ClientsPage() {
  const [editing, setEditing]   = useState<AdminClient | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminClients();
  const deleteMutation      = useDeleteClient();

  const handleDelete = async () => {
    if (!deleteId) return;
    try { await deleteMutation.mutateAsync(deleteId); toast.success('Client deleted.'); }
    catch (err) { toast.error(getErrorMessage(err)); }
    finally { setDeleteId(null); }
  };

  const columns: Column<AdminClient>[] = [
    {
      key: 'client',
      header: 'Client',
      render: (c) => (
        <div className="flex items-center gap-3">
          {c.logo ? (
            <img
              src={c.logo.thumb_url ?? c.logo.url}
              alt={c.logo.alt_text ?? c.name}
              className="h-10 w-10 rounded-lg object-contain bg-ivory-warm border border-cream p-1"
              width={40}
              height={40}
              loading="lazy"
            />
          ) : (
            <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-ivory-warm text-taupe">
              <Building2 size={16} />
            </span>
          )}
          <div>
            <div className="font-medium text-charcoal">{c.name}</div>
            {c.website_url && <div className="text-xs text-taupe truncate max-w-[200px]">{c.website_url}</div>}
          </div>
        </div>
      ),
    },
    { key: 'status',     header: 'Status', render: (c) => <StatusBadge status={c.status} /> },
    { key: 'sort_order', header: 'Order',  render: (c) => c.sort_order, className: 'w-20 text-center' },
    {
      key: 'actions', header: '',
      render: (c) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => { setEditing(c); setShowForm(true); }} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(c.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Clients"
        subtitle="Brands and clients featured on the About page."
        actions={
          <button onClick={() => { setEditing(undefined); setShowForm(true); }} className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light">
            <Plus size={16} /> Add Client
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(c) => c.id} loading={isLoading} emptyMessage="No clients yet." />
      {showForm && (
        <ClientModal
          client={editing}
          onClose={() => { setShowForm(false); setEditing(undefined); }}
        />
      )}
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete client?"
        message="This will remove the client permanently."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
