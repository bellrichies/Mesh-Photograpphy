import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Plus, Pencil, Trash2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAdminBlogPosts, useDeleteBlogPost, type AdminBlogPost } from '@/api/admin/blog';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import ConfirmDialog from '@/components/admin/ConfirmDialog';
import Pagination from '@/components/admin/Pagination';
import { getErrorMessage } from '@/utils/api-errors';

const STATUS_TABS = ['all', 'published', 'draft'] as const;

export default function BlogPostsPage() {
  const navigate   = useNavigate();
  const [page, setPage]         = useState(1);
  const [q, setQ]               = useState('');
  const [status, setStatus]     = useState<'all' | 'published' | 'draft'>('all');
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const filters  = { page, q, ...(status !== 'all' ? { status } : {}) };
  const { data, isLoading } = useAdminBlogPosts(filters);
  const deleteMutation      = useDeleteBlogPost();

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync(deleteId);
      toast.success('Post deleted.');
    } catch (err) {
      toast.error(getErrorMessage(err));
    } finally {
      setDeleteId(null);
    }
  };

  const columns: Column<AdminBlogPost>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (p) => (
        <div className="flex items-center gap-3">
          {p.cover?.thumb_url && (
            <img
              src={p.cover.thumb_url}
              alt=""
              className="w-10 h-10 rounded object-cover flex-shrink-0"
              width={40}
              height={40}
            />
          )}
          <div>
            <div className="font-medium text-charcoal line-clamp-1">{p.title}</div>
            <div className="text-xs text-taupe">{p.slug}</div>
          </div>
        </div>
      ),
    },
    {
      key: 'category',
      header: 'Category',
      render: (p) => p.categories[0]?.name ?? <span className="text-taupe text-xs">-</span>,
    },
    {
      key: 'status',
      header: 'Status',
      render: (p) => <StatusBadge status={p.status} />,
    },
    {
      key: 'date',
      header: 'Published',
      render: (p) =>
        p.published_at
          ? new Date(p.published_at).toLocaleDateString()
          : <span className="text-taupe text-xs">-</span>,
    },
    {
      key: 'actions',
      header: '',
      render: (p) => (
        <div className="flex items-center gap-2 justify-end">
          <button
            onClick={() => navigate(`/admin/blog/${p.id}/edit`)}
            className="p-1.5 text-taupe hover:text-charcoal rounded transition-colors"
            aria-label="Edit"
          >
            <Pencil size={15} />
          </button>
          <button
            onClick={() => setDeleteId(p.id)}
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
        title="Blog Posts"
        subtitle="Write and manage your blog content."
        actions={
          <Link
            to="/admin/blog/new"
            className="flex items-center gap-2 px-4 py-2 bg-bronze text-ivory text-sm font-body rounded-lg hover:bg-bronze-light transition-colors"
          >
            <Plus size={16} />
            New Post
          </Link>
        }
      />

      <div className="flex items-center gap-3 mb-4">
        <div className="flex border border-cream rounded-lg overflow-hidden">
          {STATUS_TABS.map((tab) => (
            <button
              key={tab}
              onClick={() => { setStatus(tab); setPage(1); }}
              className={`px-3 py-1.5 text-xs font-body capitalize transition-colors ${
                status === tab
                  ? 'bg-espresso text-ivory'
                  : 'text-taupe hover:text-charcoal hover:bg-ivory-warm'
              }`}
            >
              {tab}
            </button>
          ))}
        </div>

        <div className="relative">
          <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-taupe" />
          <input
            id="admin-blog-search"
            name="admin_blog_search"
            type="search"
            autoComplete="search"
            value={q}
            onChange={(e) => { setQ(e.target.value); setPage(1); }}
            placeholder="Search posts..."
            className="pl-9 pr-3 py-1.5 border border-cream rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-bronze"
          />
        </div>
      </div>

      <DataTable
        columns={columns}
        data={data?.data ?? []}
        keyExtractor={(p) => p.id}
        loading={isLoading}
        emptyMessage="No posts yet. Write your first post."
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
        title="Delete post?"
        message="This will soft-delete the post and remove it from the public blog."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
