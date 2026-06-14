import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAdminBooking, useUpdateBookingStatus } from '@/api/admin/bookings';
import PageHeader from '@/components/admin/PageHeader';
import StatusBadge from '@/components/admin/StatusBadge';
import { getErrorMessage } from '@/utils/api-errors';

const STATUSES = ['new', 'contacted', 'quoted', 'booked', 'cancelled'] as const;

export default function BookingDetailPage() {
  const { id }    = useParams<{ id: string }>();
  const bookingId = Number(id) || 0;

  const { data: booking, isLoading } = useAdminBooking(bookingId);
  const updateStatus = useUpdateBookingStatus();

  const handleStatusChange = async (status: string) => {
    try {
      await updateStatus.mutateAsync({ id: bookingId, status });
      toast.success('Status updated.');
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  if (isLoading) return <div className="animate-pulse h-64 bg-cream rounded-xl" />;
  if (!booking)  return <p className="text-taupe font-body text-sm">Booking not found.</p>;

  return (
    <div className="max-w-2xl">
      <PageHeader title="Booking Request" backTo="/admin/bookings" />

      <div className="space-y-6">
        <div className="bg-white rounded-xl border border-cream p-6 space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="font-display text-lg text-charcoal">{booking.name}</p>
              <p className="text-sm text-taupe font-body">{booking.email}{booking.phone ? ` · ${booking.phone}` : ''}</p>
            </div>
            <StatusBadge status={booking.status} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-1">Event Type</p>
              <p className="text-sm text-charcoal font-body">{booking.event_type}</p>
            </div>
            {booking.event_date && (
              <div>
                <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-1">Event Date</p>
                <p className="text-sm text-charcoal font-body">{new Date(booking.event_date).toLocaleDateString()}</p>
              </div>
            )}
            {booking.event_location && (
              <div className="col-span-2">
                <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-1">Location</p>
                <p className="text-sm text-charcoal font-body">{booking.event_location}</p>
              </div>
            )}
          </div>

          <div>
            <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-1">Message</p>
            <p className="text-sm text-charcoal font-body whitespace-pre-wrap">{booking.message}</p>
          </div>

          <p className="text-xs text-taupe font-body">
            Received {new Date(booking.created_at).toLocaleString()}
          </p>
        </div>

        <div className="bg-white rounded-xl border border-cream p-6">
          <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-3">Update Status</p>
          <div className="flex flex-wrap gap-2">
            {STATUSES.map((s) => (
              <button
                key={s}
                onClick={() => handleStatusChange(s)}
                disabled={booking.status === s || updateStatus.isPending}
                className={`px-3 py-1.5 text-xs font-body rounded-full capitalize transition-colors disabled:opacity-50 ${
                  booking.status === s
                    ? 'bg-espresso text-ivory cursor-default'
                    : 'bg-cream text-charcoal hover:bg-bronze hover:text-ivory'
                }`}
              >
                {s}
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
