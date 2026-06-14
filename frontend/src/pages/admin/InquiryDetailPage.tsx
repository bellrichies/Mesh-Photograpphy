import { useState } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAdminInquiry, useUpdateInquiryStatus, useAddInquiryNote } from '@/api/admin/inquiries';
import PageHeader from '@/components/admin/PageHeader';
import StatusBadge from '@/components/admin/StatusBadge';
import { getErrorMessage } from '@/utils/api-errors';

const STATUSES = ['new', 'in_progress', 'replied', 'closed'] as const;

export default function InquiryDetailPage() {
  const { id }    = useParams<{ id: string }>();
  const inquiryId = Number(id) || 0;

  const { data: inquiry, isLoading } = useAdminInquiry(inquiryId);
  const updateStatus  = useUpdateInquiryStatus();
  const addNote       = useAddInquiryNote();

  const [note, setNote]           = useState('');
  const [submitting, setSubmitting] = useState(false);

  const handleStatusChange = async (status: string) => {
    try {
      await updateStatus.mutateAsync({ id: inquiryId, status });
      toast.success('Status updated.');
    } catch (err) { toast.error(getErrorMessage(err)); }
  };

  const handleAddNote = async () => {
    if (!note.trim()) return;
    setSubmitting(true);
    try {
      await addNote.mutateAsync({ id: inquiryId, note });
      setNote('');
      toast.success('Note added.');
    } catch (err) { toast.error(getErrorMessage(err)); }
    finally { setSubmitting(false); }
  };

  if (isLoading) return <div className="animate-pulse h-64 bg-cream rounded-xl" />;
  if (!inquiry)  return <p className="text-taupe font-body text-sm">Inquiry not found.</p>;

  return (
    <div className="max-w-2xl">
      <PageHeader title="Inquiry" backTo="/admin/inquiries" />

      <div className="space-y-6">
        <div className="bg-white rounded-xl border border-cream p-6 space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="font-display text-lg text-charcoal">{inquiry.name}</p>
              <p className="text-sm text-taupe font-body">{inquiry.email}{inquiry.phone ? ` · ${inquiry.phone}` : ''}</p>
            </div>
            <StatusBadge status={inquiry.status} />
          </div>

          {inquiry.subject && (
            <div>
              <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-1">Subject</p>
              <p className="text-sm text-charcoal font-body">{inquiry.subject}</p>
            </div>
          )}

          <div>
            <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-1">Message</p>
            <p className="text-sm text-charcoal font-body whitespace-pre-wrap">{inquiry.message}</p>
          </div>

          <p className="text-xs text-taupe font-body">
            Received {new Date(inquiry.created_at).toLocaleString()}
          </p>
        </div>

        <div className="bg-white rounded-xl border border-cream p-6">
          <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-3">Update Status</p>
          <div className="flex flex-wrap gap-2">
            {STATUSES.map((s) => (
              <button
                key={s}
                onClick={() => handleStatusChange(s)}
                disabled={inquiry.status === s || updateStatus.isPending}
                className={`px-3 py-1.5 text-xs font-body rounded-full capitalize transition-colors disabled:opacity-50 ${
                  inquiry.status === s
                    ? 'bg-espresso text-ivory cursor-default'
                    : 'bg-cream text-charcoal hover:bg-bronze hover:text-ivory'
                }`}
              >
                {s.replace('_', ' ')}
              </button>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-xl border border-cream p-6">
          <p className="text-xs font-medium text-taupe font-body uppercase tracking-wider mb-3">
            Notes ({inquiry.notes?.length ?? 0})
          </p>
          {inquiry.notes?.map((n) => (
            <div key={n.id} className="border-l-2 border-cream pl-3 mb-3">
              <p className="text-sm text-charcoal font-body">{n.note}</p>
              <p className="text-xs text-taupe font-body mt-0.5">
                {n.created_by.name} · {new Date(n.created_at).toLocaleDateString()}
              </p>
            </div>
          ))}
          <div className="mt-4">
            <textarea
              value={note}
              onChange={(e) => setNote(e.target.value)}
              rows={3}
              placeholder="Add an internal note…"
              className="w-full px-3 py-2 border border-cream rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-bronze resize-none"
            />
            <button
              onClick={handleAddNote}
              disabled={submitting || !note.trim()}
              className="mt-2 px-4 py-2 text-sm font-body bg-bronze text-ivory rounded-lg hover:bg-bronze-light disabled:opacity-60"
            >
              {submitting ? 'Adding…' : 'Add Note'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
