import { cn } from '@/utils/cn';

type Status =
  | 'published' | 'draft' | 'archived' | 'scheduled'
  | 'new' | 'in_progress' | 'replied' | 'closed'
  | 'contacted' | 'quoted' | 'booked' | 'cancelled'
  | 'active' | 'inactive';

const STATUS_STYLES: Record<Status, string> = {
  published:   'bg-green-100 text-green-800',
  active:      'bg-green-100 text-green-800',
  booked:      'bg-green-100 text-green-800',
  new:         'bg-blue-100  text-blue-800',
  in_progress: 'bg-yellow-100 text-yellow-800',
  contacted:   'bg-yellow-100 text-yellow-800',
  quoted:      'bg-purple-100 text-purple-800',
  scheduled:   'bg-purple-100 text-purple-800',
  replied:     'bg-teal-100  text-teal-800',
  draft:       'bg-gray-100  text-gray-600',
  inactive:    'bg-gray-100  text-gray-600',
  archived:    'bg-gray-100  text-gray-500',
  closed:      'bg-gray-200  text-gray-600',
  cancelled:   'bg-red-100   text-red-700',
};

const STATUS_LABELS: Partial<Record<Status, string>> = {
  in_progress: 'In Progress',
};

interface Props {
  status: string;
  className?: string;
}

export default function StatusBadge({ status, className }: Props) {
  const styles = STATUS_STYLES[status as Status] ?? 'bg-gray-100 text-gray-600';
  const label  = STATUS_LABELS[status as Status] ?? status.replace(/_/g, ' ');

  return (
    <span
      className={cn(
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-body capitalize',
        styles,
        className
      )}
    >
      {label}
    </span>
  );
}
