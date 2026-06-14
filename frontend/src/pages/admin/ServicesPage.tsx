import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAdminServices, useDeleteService, type AdminService } from '@/api/admin/services';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import { getErrorMessage } from '@/utils/api-errors';

export default function ServicesPage() {
  const navigate           = useNavigate();
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminServices();
  const deleteMutation      = useDeleteService();

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync(deleteId);
      toast.success('Service deleted.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    } finally {
      setDeleteId(null);
    }
  };

  const columns: Column<AdminService>[] = [
    {
      key: 'title',
      header: 'Service',
      render: (s) => (
        <div className="flex items-center gap-3">
          {s.cover?.thumb_url && (
            <img src={s.cover.thumb_url} alt="" className="w-10 h-10 rounded object-cover flex-shrink-0" width={40} height={40} />
          )}
          <div>
            <div className="font-medium text-charcoal">{s.title}</div>
            <div className="text-xs text-taupe">{s.price_display ?? s.slug}</div>
          </div>
        </div>
      ),
    },
    { key: 'order',  header: 'Order',  render: (s) => s.sort_order, className: 'w-20 text-center' },
    { key: 'status', header: 'Status', render: (s) => <StatusBadge status={s.status} /> },
    {
      key: 'actions', header: '',
      render: (s) => (
        <div className="flex items-center gap-2 justify-end">
          <button onClick={() => navigate(`/admin/services/${s.id}/edit`)} className="p-1.5 text-taupe hover:text-charcoal rounded" aria-label="Edit"><Pencil size={15} /></button>
          <button onClick={() => setDeleteId(s.id)} className="p-1.5 text-taupe hover:text-red-600 rounded" aria-label="Delete"><Trash2 size={15} /></button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Services"
        subtitle="Manage the services you offer."
        actions={
          <button
            onClick={() => navigate('/admin/services/new')}
            className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light transition-colors"
          >
            <Plus size={16} /> New Service
          </button>
        }
      />
      <DataTable columns={columns} data={data ?? []} keyExtractor={(s) => s.id} loading={isLoading} emptyMessage="No services yet." />
      <ConfirmDialog
        open={deleteId !== null}
        title="Delete service?"
        message="This will soft-delete the service."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
