import { useState } from 'react';
import { Plus, Pencil, Trash2, User } from 'lucide-react';
import toast from 'react-hot-toast';
import { Controller, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminTeamMembers,
  useCreateTeamMember,
  useUpdateTeamMember,
  useDeleteTeamMember,
  type AdminTeamMember,
  type AdminTeamMemberPayload,
} from '@/api/admin/team';
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
  name:          z.string().min(1, 'Name is required'),
  role:          z.string().optional(),
  bio:           z.string().optional(),
  email:         z.string().email('Enter a valid email').or(z.literal('')).optional(),
  instagram_url: z.string().url('Enter a valid URL').or(z.literal('')).optional(),
  photo_id:      nullableNumber.optional(),
  photo:         z.custom<MediaRecord | null>().nullable().optional(),
  status:        z.enum(['draft', 'published']),
  sort_order:    z.coerce.number().int().optional(),
});

type FormValues = z.infer<typeof schema>;

function TeamMemberModal({
  member, onClose,
}: { member?: AdminTeamMember; onClose: () => void }) {
  const isEdit = !!member;
  const createMutation = useCreateTeamMember();
  const updateMutation = useUpdateTeamMember(member?.id ?? 0);

  const { register, handleSubmit, control, setValue, formState: { errors, isSubmitting } } =
    useForm<FormValues>({
      resolver: zodResolver(schema),
      defaultValues: {
        name:          member?.name ?? '',
        role:          member?.role ?? '',
        bio:           member?.bio ?? '',
        email:         member?.email ?? '',
        instagram_url: member?.instagram_url ?? '',
        photo_id:      member?.photo_id ?? null,
        photo:         member?.photo ?? null,
        status:        member?.status ?? 'published',
        sort_order:    member?.sort_order ?? 0,
      },
    });

  const onSubmit = async (values: FormValues) => {
    const payload: AdminTeamMemberPayload = {
      name:          values.name,
      role:          values.role || null,
      bio:           values.bio || null,
      email:         values.email || null,
      instagram_url: values.instagram_url || null,
      photo_id:      values.photo_id ?? values.photo?.id ?? null,
      status:        values.status,
      sort_order:    values.sort_order ?? 0,
    };
    try {
      if (isEdit) { await updateMutation.mutateAsync(payload); toast.success('Team member saved.'); }
      else        { await createMutation.mutateAsync(payload); toast.success('Team member added.'); }
      onClose();
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-lg p-6 max-h-[92vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'Add'} Team Member</h2>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <FormField label="Name" htmlFor="name" error={errors.name?.message} required>
            <input id="name" type="text" className={fieldClass(!!errors.name)} {...register('name')} />
          </FormField>
          <FormField label="Role / Title" htmlFor="role">
            <input id="role" type="text" className={fieldClass(false)} {...register('role')} />
          </FormField>
          <FormField label="Bio" htmlFor="bio" error={errors.bio?.message}>
            <textarea id="bio" rows={4} className={fieldClass(!!errors.bio)} {...register('bio')} />
          </FormField>
          <FormField label="Photo" hint="Optional headshot displayed on the About page.">
            <Controller
              name="photo"
              control={control}
              render={({ field }) => (
                <MediaPicker
                  value={field.value ?? null}
                  onChange={(media) => {
                    field.onChange(media);
                    setValue('photo_id', media?.id ?? null, { shouldDirty: true });
                  }}
                  label="Select photo"
                />
              )}
            />
          </FormField>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <FormField label="Email" htmlFor="email" error={errors.email?.message}>
              <input id="email" type="email" className={fieldClass(!!errors.email)} {...register('email')} />
            </FormField>
            <FormField label="Instagram URL" htmlFor="instagram_url" error={errors.instagram_url?.message}>
              <input id="instagram_url" type="url" placeholder="https://instagram.com/…" className={fieldClass(!!errors.instagram_url)} {...register('instagram_url')} />
            </FormField>
          </div>
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

export default function TeamPage() {
  const [editing, setEditing]   = useState<AdminTeamMember | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminTeamMembers();
  const deleteMutation      = useDeleteTeamMember();

  const handleDelete = async () => {
    if (!deleteId) return;
    try { await deleteMutation.mutateAsync(deleteId); toast.success('Team member deleted.'); }
    catch (err) { toast.error(getErrorMessage(err)); }
    finally { setDeleteId(null); }
  };

  const columns: Column<AdminTeamMember>[] = [
    {
      key: 'member',
      header: 'Member',
      render: (m) => (
        <div className="flex items-center gap-3">
          {m.photo ? (
            <img
              src={m.photo.thumb_url ?? m.photo.url}
              alt={m.photo.alt_text ?? m.name}
              className="h-10 w-10 rounded-full object-cover border border-cream"
              width={40}
              height={40}
              loading="lazy"
            />
          ) : (
            <span className="flex h-10 w-10 items-center justify-center rounded-full bg-ivory-warm text-taupe">
              <User size={16} />
            </span>
          )}
          <div>
            <div className="font-medium text-charcoal">{m.name}</div>
            {m.role && <div className="text-xs text-taupe">{m.role}</div>}
          </div>
        </div>
      ),
    },
    { key: 'status',     header: 'Status', render: (m) => <StatusBadge status={m.status} /> },
    { key: 'sort_order', header: 'Order',  render: (m) => m.sort_order, className: 'w-20 text-center' },
    {
      key: 'actions', header: '',
      render: (m) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => { setEditing(m); setShowForm(true); }} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(m.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Team"
        subtitle="The people behind the lens, shown on the About page."
        actions={
          <button onClick={() => { setEditing(undefined); setShowForm(true); }} className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light">
            <Plus size={16} /> Add Member
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(m) => m.id} loading={isLoading} emptyMessage="No team members yet." />
      {showForm && (
        <TeamMemberModal
          member={editing}
          onClose={() => { setShowForm(false); setEditing(undefined); }}
        />
      )}
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete team member?"
        message="This will remove the team member permanently."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
