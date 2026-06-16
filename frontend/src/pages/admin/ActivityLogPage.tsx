import { useState } from 'react';
import { useActivityLogs } from '@/api/admin/activity-logs';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import Pagination from '@/components/admin/Pagination';
import type { ActivityLogEntry } from '@/api/admin/activity-logs';

export default function ActivityLogPage() {
  const [page, setPage] = useState(1);

  const { data, isLoading } = useActivityLogs({ page, per_page: 50 });

  const columns: Column<ActivityLogEntry>[] = [
    {
      key: 'action',
      header: 'Action',
      render: (entry) => (
        <div>
          <span className="font-body text-xs font-medium bg-ivory border border-cream text-charcoal px-2 py-0.5 rounded">
            {entry.action}
          </span>
          {entry.description && (
            <div className="font-body text-xs text-taupe mt-1">{entry.description}</div>
          )}
        </div>
      ),
    },
    {
      key: 'resource',
      header: 'Resource',
      render: (entry) =>
        entry.model_type ? (
          <span className="font-body text-xs text-charcoal">
            {entry.model_type}
            {entry.model_id ? <span className="text-taupe"> #{entry.model_id}</span> : null}
          </span>
        ) : (
          <span className="text-taupe text-xs">—</span>
        ),
    },
    {
      key: 'user',
      header: 'User',
      render: (entry) =>
        entry.user ? (
          <div>
            <div className="font-body text-xs font-medium text-charcoal">{entry.user.name}</div>
            <div className="font-body text-xs text-taupe">{entry.user.email}</div>
          </div>
        ) : (
          <span className="text-taupe text-xs">System</span>
        ),
    },
    {
      key: 'ip_address',
      header: 'IP',
      render: (entry) => (
        <span className="font-body text-xs text-taupe">{entry.ip_address ?? '—'}</span>
      ),
    },
    {
      key: 'created_at',
      header: 'When',
      render: (entry) => (
        <span className="font-body text-xs text-taupe">
          {new Date(entry.created_at).toLocaleString()}
        </span>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Activity Log"
        subtitle="Audit trail of admin actions."
      />

      <DataTable
        columns={columns}
        data={data?.data ?? []}
        keyExtractor={(e) => e.id}
        loading={isLoading}
        emptyMessage="No activity recorded yet."
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
    </div>
  );
}
