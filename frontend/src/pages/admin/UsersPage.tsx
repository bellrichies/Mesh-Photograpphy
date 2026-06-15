import { useState } from 'react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  useAdminUsers,
  useAdminRoles,
  useCreateUser,
  useUpdateUser,
  useDeleteUser,
  type AdminUserPayload,
} from '@/api/admin/users';
import type { AdminUser } from '@/types/models';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import FormField, { fieldClass } from '@/components/admin/FormField';
import { getErrorMessage } from '@/utils/api-errors';

const createSchema = z.object({
  email:      z.string().email('Valid email required'),
  first_name: z.string().min(1, 'First name required'),
  last_name:  z.string().min(1, 'Last name required'),
  password:   z.string().min(8, 'Minimum 8 characters'),
  status:     z.enum(['active', 'inactive']).optional(),
});

const editSchema = z.object({
  email:      z.string().optional(),
  first_name: z.string().min(1, 'First name required'),
  last_name:  z.string().min(1, 'Last name required'),
  password:   z.string().optional(),
  status:     z.enum(['active', 'inactive']).optional(),
});

type CreateValues = z.infer<typeof createSchema>;
type EditValues   = z.infer<typeof editSchema>;

function UserModal({ user, onClose }: { user?: AdminUser; onClose: () => void }) {
  const isEdit    = !!user;
  const createMut = useCreateUser();
  const updateMut = useUpdateUser(user?.id ?? 0);
  const { data: allRoles } = useAdminRoles();

  // Track selected role IDs separately (not in RHF since it's a multi-select).
  // Initialise from the user's existing roles once the roles list is available.
  const resolvedRoleIds =
    allRoles && user
      ? allRoles.filter((r) => user.roles.includes(r.name)).map((r) => r.id)
      : [];

  const [roleIds, setRoleIds] = useState<number[]>(resolvedRoleIds);

  const toggleRole = (id: number) =>
    setRoleIds((prev) => prev.includes(id) ? prev.filter((r) => r !== id) : [...prev, id]);

  const { register, handleSubmit, formState: { errors, isSubmitting } } =
    useForm<CreateValues | EditValues>({
      resolver: zodResolver(isEdit ? editSchema : createSchema) as never,
      defaultValues: {
        email:      user?.email      ?? '',
        first_name: user?.first_name ?? '',
        last_name:  user?.last_name  ?? '',
        password:   '',
        status:     user?.status     ?? 'active',
      },
    });

  const onSubmit = async (values: CreateValues | EditValues) => {
    try {
      if (isEdit) {
        const payload: AdminUserPayload = {
          first_name: values.first_name,
          last_name:  values.last_name,
          status:     values.status,
          role_ids:   roleIds,
          ...(values.password ? { password: values.password } : {}),
        };
        await updateMut.mutateAsync(payload);
        toast.success('User updated.');
      } else {
        const v = values as CreateValues;
        await createMut.mutateAsync({
          email:      v.email,
          first_name: v.first_name,
          last_name:  v.last_name,
          password:   v.password,
          status:     v.status,
          role_ids:   roleIds,
        });
        toast.success('User created.');
      }
      onClose();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-soft w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 className="font-display text-xl text-charcoal mb-5">{isEdit ? 'Edit' : 'New'} User</h2>
        <form onSubmit={handleSubmit(onSubmit as never)} className="space-y-4">
          {!isEdit && (
            <FormField label="Email" htmlFor="email" error={(errors as { email?: { message?: string } }).email?.message} required>
              <input id="email" type="email" autoComplete="email" className={fieldClass(!!(errors as { email?: unknown }).email)} {...register('email')} />
            </FormField>
          )}

          <div className="grid grid-cols-2 gap-4">
            <FormField label="First Name" htmlFor="first_name" error={errors.first_name?.message} required>
              <input id="first_name" type="text" autoComplete="given-name" className={fieldClass(!!errors.first_name)} {...register('first_name')} />
            </FormField>
            <FormField label="Last Name" htmlFor="last_name" error={errors.last_name?.message} required>
              <input id="last_name" type="text" autoComplete="family-name" className={fieldClass(!!errors.last_name)} {...register('last_name')} />
            </FormField>
          </div>

          <FormField label={isEdit ? 'New Password (leave blank to keep)' : 'Password'} htmlFor="password" error={errors.password?.message} required={!isEdit}>
            <input id="password" type="password" autoComplete="new-password" className={fieldClass(!!errors.password)} {...register('password')} />
          </FormField>

          <FormField label="Status" htmlFor="status">
            <select id="status" className={fieldClass(false)} {...register('status')}>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </FormField>

          {/* Role selection */}
          {allRoles && allRoles.length > 0 && (
            <FormField label="Role" htmlFor="roles" hint="Assign one or more roles to this user.">
              <div className="flex flex-wrap gap-2 mt-1">
                {allRoles.map((role) => (
                  <label
                    key={role.id}
                    className="flex items-center gap-2 cursor-pointer font-body text-sm text-charcoal"
                  >
                    <input
                      id={`role-${role.id}`}
                      name="role_ids"
                      type="checkbox"
                      className="rounded border-cream accent-bronze"
                      checked={roleIds.includes(role.id)}
                      onChange={() => toggleRole(role.id)}
                    />
                    <span className="capitalize">{role.name}</span>
                    {role.description && (
                      <span className="text-taupe text-xs">- {role.description}</span>
                    )}
                  </label>
                ))}
              </div>
              {roleIds.length === 0 && (
                <p className="mt-1 text-xs text-taupe font-body">No role assigned - user will have minimal access.</p>
              )}
            </FormField>
          )}

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

export default function UsersPage() {
  const [editing, setEditing]   = useState<AdminUser | undefined>();
  const [showForm, setShowForm] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminUsers();
  const deleteMutation      = useDeleteUser();

  const handleDelete = async () => {
    if (!deleteId) return;
    try { await deleteMutation.mutateAsync(deleteId); toast.success('User deleted.'); }
    catch (err) { toast.error(getErrorMessage(err)); }
    finally { setDeleteId(null); }
  };

  const columns: Column<AdminUser>[] = [
    {
      key: 'name',
      header: 'User',
      render: (u) => (
        <div>
          <div className="font-medium text-charcoal">{u.first_name} {u.last_name}</div>
          <div className="text-xs text-taupe">{u.email}</div>
        </div>
      ),
    },
    {
      key: 'roles',
      header: 'Roles',
      render: (u) => (
        <div className="flex flex-wrap gap-1">
          {u.roles.length > 0 ? u.roles.map((r) => (
            <span key={r} className="px-2 py-0.5 text-xs font-body bg-cream text-charcoal rounded-full capitalize">{r}</span>
          )) : <span className="text-taupe text-xs">—</span>}
        </div>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (u) => <StatusBadge status={u.status ?? 'active'} />,
    },
    {
      key: 'last_login',
      header: 'Last Login',
      render: (u) => u.last_login_at
        ? new Date(u.last_login_at).toLocaleDateString()
        : <span className="text-taupe text-xs">Never</span>,
    },
    {
      key: 'actions', header: '',
      render: (u) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => { setEditing(u); setShowForm(true); }} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(u.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Users"
        subtitle="Manage admin users."
        actions={
          <button onClick={() => { setEditing(undefined); setShowForm(true); }} className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light">
            <Plus size={16} /> New User
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(u) => u.id} loading={isLoading} emptyMessage="No users found." />
      {showForm && (
        <UserModal user={editing} onClose={() => { setShowForm(false); setEditing(undefined); }} />
      )}
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete user?"
        message="This user will be permanently removed."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
        variant="danger"
      />
    </div>
  );
}
