import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Eye, Download } from 'lucide-react';
import { useAdminInquiries, type AdminInquiryFilters } from '@/api/admin/inquiries';
import type { Inquiry } from '@/types/models';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import Pagination from '@/components/admin/Pagination';
import { apiClient } from '@/api/client';

const STATUS_TABS = ['all', 'new', 'in_progress', 'replied', 'closed'] as const;

export default function InquiriesPage() {
  const navigate   = useNavigate();
  const [page, setPage]     = useState(1);
  const [status, setStatus] = useState<'all' | string>('all');

  const filters: AdminInquiryFilters = { page, ...(status !== 'all' ? { status } : {}) };
  const { data, isLoading } = useAdminInquiries(filters);

  const handleExport = async () => {
    const params = new URLSearchParams();
    if (status !== 'all') params.set('status', status);

    // Use axios to get the CSV with auth headers, then trigger download
    const res = await apiClient.get(`/admin/inquiries/export?${params.toString()}`, {
      responseType: 'blob',
    });
    const url  = URL.createObjectURL(res.data as Blob);
    const link = document.createElement('a');
    link.href     = url;
    link.download = `inquiries-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  };

  const columns: Column<Inquiry>[] = [
    {
      key: 'name',
      header: 'From',
      render: (i) => (
        <div>
          <div className="font-medium text-charcoal">{i.name}</div>
          <div className="text-xs text-taupe">{i.email}</div>
        </div>
      ),
    },
    {
      key: 'subject',
      header: 'Subject',
      render: (i) => i.subject ?? <span className="text-taupe text-xs">—</span>,
    },
    {
      key: 'status',
      header: 'Status',
      render: (i) => <StatusBadge status={i.status} />,
    },
    {
      key: 'date',
      header: 'Received',
      render: (i) => new Date(i.created_at).toLocaleDateString(),
    },
    {
      key: 'actions',
      header: '',
      render: (i) => (
        <button
          onClick={() => navigate(`/admin/inquiries/${i.id}`)}
          className="p-1.5 text-taupe hover:text-charcoal rounded"
          aria-label="View"
        >
          <Eye size={15} />
        </button>
      ),
      className: 'w-16 text-right',
    },
  ];

  return (
    <div>
      <PageHeader
        title="Inquiries"
        subtitle="Contact form submissions."
        actions={
          <button
            type="button"
            onClick={handleExport}
            className="flex items-center gap-2 px-3 py-1.5 text-xs font-body text-taupe border border-cream rounded-lg hover:text-charcoal hover:border-charcoal transition-colors"
          >
            <Download size={13} /> Export CSV
          </button>
        }
      />

      <div className="flex border border-cream rounded-lg overflow-hidden mb-4 w-fit">
        {STATUS_TABS.map((tab) => (
          <button
            key={tab}
            onClick={() => { setStatus(tab); setPage(1); }}
            className={`px-3 py-1.5 text-xs font-body capitalize transition-colors ${
              status === tab ? 'bg-espresso text-ivory' : 'text-taupe hover:text-charcoal hover:bg-ivory-warm'
            }`}
          >
            {tab.replace('_', ' ')}
          </button>
        ))}
      </div>

      <DataTable columns={columns} data={data?.data ?? []} keyExtractor={(i) => i.id} loading={isLoading} emptyMessage="No inquiries found." />

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
  );
}
