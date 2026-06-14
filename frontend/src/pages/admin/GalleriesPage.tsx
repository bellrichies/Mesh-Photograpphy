import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Plus, Pencil, Trash2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAdminGalleries, useDeleteGallery, type AdminGallery } from '@/api/admin/galleries';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import Pagination from '@/components/admin/Pagination';
import { getErrorMessage } from '@/utils/api-errors';

export default function GalleriesPage() {
  const navigate = useNavigate();
  const [page, setPage]        = useState(1);
  const [q, setQ]              = useState('');
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { data, isLoading } = useAdminGalleries({ page, q });
  const deleteMutation      = useDeleteGallery();

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync(deleteId);
      toast.success('Gallery deleted.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    } finally {
      setDeleteId(null);
    }
  };

  const columns: Column<AdminGallery>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (g) => (
        <div className="flex items-center gap-3">
          {g.cover?.thumb_url && (
            <img
              src={g.cover.thumb_url}
              alt=""
              className="w-10 h-10 rounded object-cover flex-shrink-0"
              width={40}
              height={40}
            />
          )}
          <div>
            <div className="font-medium text-charcoal">{g.title}</div>
            <div className="text-xs text-taupe">{g.slug}</div>
          </div>
        </div>
      ),
    },
    {
      key: 'category',
      header: 'Category',
      render: (g) => g.category ?? <span className="text-taupe text-xs">—</span>,
    },
    {
      key: 'media',
      header: 'Images',
      render: (g) => g.media_count,
      className: 'text-center w-20',
    },
    {
      key: 'status',
      header: 'Status',
      render: (g) => <StatusBadge status={g.status} />,
    },
    {
      key: 'actions',
      header: '',
      render: (g) => (
        <div className="flex items-center gap-2 justify-end">
          <button
            onClick={() => navigate(`/admin/galleries/${g.id}/edit`)}
            className="p-1.5 text-taupe hover:text-charcoal rounded transition-colors"
            aria-label="Edit"
          >
            <Pencil size={15} />
          </button>
          <button
            onClick={() => setDeleteId(g.id)}
            className="p-1.5 text-taupe hover:text-red-600 rounded transition-colors"
            aria-label="Delete"
          >
            <Trash2 size={15} />
          </button>
        </div>
      ),
      className: 'w-24',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Galleries"
        subtitle="Manage your photography galleries."
        actions={
          <Link
            to="/admin/galleries/new"
            className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light transition-colors"
          >
            <Plus size={16} />
            New Gallery
          </Link>
        }
      />

      <div className="mb-4">
        <div className="relative max-w-xs">
          <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-taupe" />
          <input
            type="text"
            value={q}
            onChange={(e) => { setQ(e.target.value); setPage(1); }}
            placeholder="Search galleries…"
            className="w-full pl-9 pr-3 py-2 border border-cream rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-bronze"
          />
        </div>
      </div>

      <DataTable
        columns={columns}
        data={data?.data ?? []}
        keyExtractor={(g) => g.id}
        loading={isLoading}
        emptyMessage="No galleries yet. Create your first gallery."
      />

      {data && (
        <Pagination
          currentPage={data.meta.current_page}
          lastPage={data.meta.last_page}
          total={data.meta.total}
          perPage={data.meta.per_page}
          onPageChange={setPage}
        />
      )}

      <ConfirmDialog
        open={deleteId !== null}
        title="Delete gallery?"
        message="This will soft-delete the gallery. Media files are not affected."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
