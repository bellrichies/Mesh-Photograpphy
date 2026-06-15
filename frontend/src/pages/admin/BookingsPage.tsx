import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Eye } from 'lucide-react';
import { useAdminBookings, type AdminBookingFilters } from '@/api/admin/bookings';
import type { BookingRequest } from '@/types/models';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import StatusBadge from '@/components/admin/StatusBadge';
import Pagination from '@/components/admin/Pagination';

const STATUS_TABS = ['all', 'new', 'contacted', 'booked', 'declined', 'cancelled'] as const;

export default function BookingsPage() {
  const navigate   = useNavigate();
  const [page, setPage]     = useState(1);
  const [status, setStatus] = useState<'all' | string>('all');

  const filters: AdminBookingFilters = { page, ...(status !== 'all' ? { status } : {}) };
  const { data, isLoading } = useAdminBookings(filters);

  const columns: Column<BookingRequest>[] = [
    {
      key: 'name',
      header: 'Client',
      render: (b) => (
        <div>
          <div className="font-medium text-charcoal">{b.name}</div>
          <div className="text-xs text-taupe">{b.email}</div>
        </div>
      ),
    },
    { key: 'event_type', header: 'Event Type', render: (b) => b.event_type },
    {
      key: 'event_date',
      header: 'Event Date',
      render: (b) => b.event_date
        ? new Date(b.event_date).toLocaleDateString()
        : <span className="text-taupe text-xs">TBD</span>,
    },
    { key: 'status', header: 'Status', render: (b) => <StatusBadge status={b.status} /> },
    {
      key: 'date',
      header: 'Received',
      render: (b) => new Date(b.created_at).toLocaleDateString(),
    },
    {
      key: 'actions',
      header: '',
      render: (b) => (
        <button
          onClick={() => navigate(`/admin/bookings/${b.id}`)}
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
      <PageHeader title="Bookings" subtitle="Event booking requests." />

      <div className="flex border border-cream rounded-lg overflow-hidden mb-4 w-fit flex-wrap">
        {STATUS_TABS.map((tab) => (
          <button
            key={tab}
            onClick={() => { setStatus(tab); setPage(1); }}
            className={`px-3 py-1.5 text-xs font-body capitalize transition-colors ${
              status === tab ? 'bg-espresso text-ivory' : 'text-taupe hover:text-charcoal hover:bg-ivory-warm'
            }`}
          >
            {tab}
          </button>
        ))}
      </div>

      <DataTable columns={columns} data={data?.data ?? []} keyExtractor={(b) => b.id} loading={isLoading} emptyMessage="No booking requests found." />

      {data?.meta && (
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
