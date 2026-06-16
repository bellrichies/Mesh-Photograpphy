import { useMemo, useState } from 'react';
import { Download, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import PageHeader from '@/components/admin/PageHeader';
import DataTable, { type Column } from '@/components/admin/DataTable';
import Pagination from '@/components/admin/Pagination';
import { cn } from '@/utils/cn';
import {
  downloadNewsletterSubscribersCsv,
  useNewsletterSubscribers,
  type NewsletterSubscriber,
} from '@/api/admin/newsletter';
import { getErrorMessage } from '@/utils/api-errors';

const statusClass = {
  active: 'bg-green-50 text-green-700 border-green-200',
  unsubscribed: 'bg-ivory text-taupe border-cream',
} satisfies Record<NewsletterSubscriber['status'], string>;

export default function NewsletterSubscribersPage() {
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState<'active' | 'unsubscribed' | ''>('active');
  const [searchInput, setSearchInput] = useState('');
  const [query, setQuery] = useState('');
  const [isExporting, setIsExporting] = useState(false);

  const filters = useMemo(() => ({
    page,
    per_page: 50,
    status,
    q: query || undefined,
  }), [page, query, status]);

  const { data, isLoading } = useNewsletterSubscribers(filters);

  const columns: Column<NewsletterSubscriber>[] = [
    {
      key: 'email',
      header: 'Email',
      render: (subscriber) => (
        <div>
          <div className="font-body text-sm text-charcoal">{subscriber.email}</div>
          <div className="font-body text-xs text-taupe">{subscriber.source || 'footer'}</div>
        </div>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (subscriber) => (
        <span className={cn('inline-flex border rounded-full px-2 py-0.5 text-xs font-body capitalize', statusClass[subscriber.status])}>
          {subscriber.status.replace('_', ' ')}
        </span>
      ),
    },
    {
      key: 'subscribed_at',
      header: 'Subscribed',
      render: (subscriber) => (
        <time className="font-body text-xs text-taupe" dateTime={subscriber.subscribed_at}>
          {new Date(subscriber.subscribed_at).toLocaleString()}
        </time>
      ),
    },
    {
      key: 'ip_address',
      header: 'IP',
      render: (subscriber) => (
        <span className="font-body text-xs text-taupe">{subscriber.ip_address ?? '-'}</span>
      ),
    },
  ];

  function applySearch(e: React.FormEvent) {
    e.preventDefault();
    setPage(1);
    setQuery(searchInput.trim());
  }

  async function exportCsv() {
    try {
      setIsExporting(true);
      await downloadNewsletterSubscribersCsv({ status, q: query || undefined });
      toast.success('Subscriber CSV downloaded.');
    } catch (error) {
      toast.error(getErrorMessage(error));
    } finally {
      setIsExporting(false);
    }
  }

  return (
    <div>
      <PageHeader
        title="Newsletter Subscribers"
        subtitle="View subscribers and export their email list."
        actions={
          <button
            type="button"
            onClick={exportCsv}
            disabled={isExporting || isLoading}
            className="inline-flex items-center gap-2 px-4 py-2 bg-bronze text-ivory rounded-lg font-body text-sm hover:bg-bronze-dark disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <Download size={16} />
            {isExporting ? 'Exporting...' : 'Download CSV'}
          </button>
        }
      />

      <div className="bg-white border border-cream rounded-xl p-4 mb-4 flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
        <form onSubmit={applySearch} className="flex gap-2 w-full lg:max-w-md">
          <div className="relative flex-1">
            <Search size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-taupe pointer-events-none" />
            <input
              id="newsletter-subscriber-search"
              type="search"
              value={searchInput}
              onChange={(e) => setSearchInput(e.target.value)}
              placeholder="Search email"
              className="w-full border border-cream bg-ivory px-9 py-2.5 rounded-lg font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze"
            />
          </div>
          <button type="submit" className="px-4 py-2.5 rounded-lg border border-cream font-body text-sm text-charcoal hover:bg-ivory-warm">
            Search
          </button>
        </form>

        <div className="inline-flex rounded-lg border border-cream bg-ivory p-1 w-full sm:w-auto">
          {[
            ['active', 'Active'],
            ['unsubscribed', 'Unsubscribed'],
            ['', 'All'],
          ].map(([value, label]) => (
            <button
              key={value || 'all'}
              type="button"
              onClick={() => {
                setStatus(value as 'active' | 'unsubscribed' | '');
                setPage(1);
              }}
              className={cn(
                'flex-1 sm:flex-none px-3 py-1.5 rounded-md font-body text-xs transition-colors',
                status === value ? 'bg-white text-charcoal shadow-sm' : 'text-taupe hover:text-charcoal'
              )}
            >
              {label}
            </button>
          ))}
        </div>
      </div>

      <DataTable
        columns={columns}
        data={data?.data ?? []}
        keyExtractor={(subscriber) => subscriber.id}
        loading={isLoading}
        emptyMessage="No subscribers found."
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
