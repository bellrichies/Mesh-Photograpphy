import { Fragment } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/utils/cn';

interface Props {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
  onPageChange: (page: number) => void;
}

export default function Pagination({
  currentPage,
  lastPage,
  total,
  perPage,
  onPageChange,
}: Props) {
  if (lastPage <= 1) return null;

  const from = (currentPage - 1) * perPage + 1;
  const to   = Math.min(currentPage * perPage, total);

  const pages = Array.from({ length: lastPage }, (_, i) => i + 1).filter(
    (p) => p === 1 || p === lastPage || Math.abs(p - currentPage) <= 2
  );

  return (
    <div className="flex items-center justify-between mt-4 font-body text-sm">
      <span className="text-taupe">
        Showing {from}–{to} of {total}
      </span>
      <div className="flex items-center gap-1">
        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className="p-1.5 rounded hover:bg-ivory-warm disabled:opacity-40 disabled:cursor-not-allowed"
          aria-label="Previous page"
        >
          <ChevronLeft size={16} />
        </button>

        {pages.map((p, i) => {
          const prev = pages[i - 1];
          return (
            <Fragment key={p}>
              {prev && p - prev > 1 && (
                <span className="px-1 text-taupe">…</span>
              )}
              <button
                onClick={() => onPageChange(p)}
                className={cn(
                  'w-8 h-8 rounded text-sm transition-colors',
                  p === currentPage
                    ? 'bg-bronze text-ivory'
                    : 'hover:bg-ivory-warm text-charcoal'
                )}
              >
                {p}
              </button>
            </Fragment>
          );
        })}

        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage === lastPage}
          className="p-1.5 rounded hover:bg-ivory-warm disabled:opacity-40 disabled:cursor-not-allowed"
          aria-label="Next page"
        >
          <ChevronRight size={16} />
        </button>
      </div>
    </div>
  );
}
