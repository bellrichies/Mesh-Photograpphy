import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import PageMeta from '@/components/ui/PageMeta';
import { useSubmitBooking, type BookingPayload } from '@/api/booking';

const schema = z.object({
  name:       z.string().min(2, 'Name is required'),
  email:      z.string().email('Please enter a valid email'),
  phone:      z.string().optional(),
  event_type: z.string().min(1, 'Please select an event type'),
  event_date: z.string().optional(),
  location:   z.string().optional(),
  notes:      z.string().optional(),
});

type FormData = z.infer<typeof schema>;

const EVENT_TYPES = [
  'Wedding',
  'Engagement',
  'Portrait',
  'Family',
  'Newborn',
  'Corporate / Headshots',
  'Event',
  'Boudoir',
  'Other',
];

export default function BookingPage() {
  const { mutateAsync, isPending }  = useSubmitBooking();

  const { register, handleSubmit, reset, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  });

  async function onSubmit(data: FormData) {
    try {
      await mutateAsync(data as BookingPayload);
      toast.success('Booking request received! We\'ll contact you within 24 hours.');
      reset();
    } catch {
      toast.error('Failed to send booking request. Please try again.');
    }
  }

  return (
    <>
      <PageMeta title="Book a Session" description="Request a photography session. We'll get back to you within 24 hours." />

      <div className="pt-24 pb-20">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Header */}
          <div className="text-center mb-14">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Booking</p>
            <h1 className="font-display text-5xl text-charcoal font-light">Reserve Your Date</h1>
            <p className="font-body text-taupe mt-3 max-w-md mx-auto">
              Tell us about your event and we'll check our availability and get back to you promptly.
            </p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-6">
            {/* Personal info */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div>
                <label htmlFor="name" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                  Full Name <span className="text-bronze">*</span>
                </label>
                <input
                  id="name"
                  type="text"
                  {...register('name')}
                  className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                  placeholder="Your name"
                />
                {errors.name && <p className="mt-1 font-body text-xs text-red-600">{errors.name.message}</p>}
              </div>

              <div>
                <label htmlFor="email" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                  Email <span className="text-bronze">*</span>
                </label>
                <input
                  id="email"
                  type="email"
                  {...register('email')}
                  className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                  placeholder="your@email.com"
                />
                {errors.email && <p className="mt-1 font-body text-xs text-red-600">{errors.email.message}</p>}
              </div>
            </div>

            <div>
              <label htmlFor="phone" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">Phone</label>
              <input
                id="phone"
                type="tel"
                {...register('phone')}
                className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                placeholder="Optional but helpful"
              />
            </div>

            {/* Event details */}
            <div className="pt-4 border-t border-cream">
              <p className="font-display text-lg text-charcoal mb-5">Event Details</p>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <label htmlFor="event_type" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                    Event Type <span className="text-bronze">*</span>
                  </label>
                  <select
                    id="event_type"
                    {...register('event_type')}
                    className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal focus:outline-none focus:border-bronze rounded-lg"
                  >
                    <option value="">Select a type…</option>
                    {EVENT_TYPES.map((t) => (
                      <option key={t} value={t}>{t}</option>
                    ))}
                  </select>
                  {errors.event_type && <p className="mt-1 font-body text-xs text-red-600">{errors.event_type.message}</p>}
                </div>

                <div>
                  <label htmlFor="event_date" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                    Event Date
                  </label>
                  <input
                    id="event_date"
                    type="date"
                    {...register('event_date')}
                    className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal focus:outline-none focus:border-bronze rounded-lg"
                  />
                </div>
              </div>

              <div className="mt-5">
                <label htmlFor="location" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                  Location / Venue
                </label>
                <input
                  id="location"
                  type="text"
                  {...register('location')}
                  className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                  placeholder="City, venue, or 'To be decided'"
                />
              </div>
            </div>

            <div>
              <label htmlFor="notes" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                Additional Notes
              </label>
              <textarea
                id="notes"
                rows={5}
                {...register('notes')}
                className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg resize-none"
                placeholder="Tell us more about your vision, special requests, or any questions you have…"
              />
            </div>

            <button
              type="submit"
              disabled={isPending}
              className="w-full py-4 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {isPending ? 'Sending…' : 'Request Booking'}
            </button>

            <p className="font-body text-xs text-taupe text-center">
              We'll respond within 24 hours to confirm availability.
            </p>
          </form>
        </div>
      </div>
    </>
  );
}
