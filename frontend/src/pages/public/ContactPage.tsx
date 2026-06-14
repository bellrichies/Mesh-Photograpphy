import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import { Mail, Phone, MapPin, Clock, Calendar, MessageSquare } from 'lucide-react';
import PageMeta from '@/components/ui/PageMeta';
import { useSubmitContact, type ContactPayload } from '@/api/contact';
import { usePublicSettings } from '@/api/settings';

const schema = z.object({
  name:    z.string().min(2, 'Name is required'),
  email:   z.string().email('Please enter a valid email'),
  subject: z.string().optional(),
  message: z.string().min(10, 'Message must be at least 10 characters'),
  phone:   z.string().optional(),
});

type FormData = z.infer<typeof schema>;

const inputClass =
  'w-full border border-cream bg-white px-4 py-3.5 font-body text-sm text-charcoal placeholder:text-taupe/60 focus:outline-none focus:border-bronze transition-colors duration-150 rounded-none';

const labelClass = 'block font-body text-xs tracking-[0.1em] uppercase text-charcoal mb-2';

export default function ContactPage() {
  const { data: settings } = usePublicSettings();
  const { mutateAsync, isPending } = useSubmitContact();

  const { register, handleSubmit, reset, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  });

  async function onSubmit(data: FormData) {
    try {
      await mutateAsync(data as ContactPayload);
      toast.success('Message sent! We\'ll be in touch soon.');
      reset();
    } catch {
      toast.error('Failed to send message. Please try again.');
    }
  }

  const phone       = settings?.contact.phone;
  const email       = settings?.contact.email;
  const address     = settings?.contact.address;
  const mapEmbedUrl = settings?.contact.map_embed_url ?? null;

  return (
    <>
      <PageMeta title="Contact" description="Get in touch with Mesh Photography. We'd love to hear about your vision." />

      {/* ── Hero ──────────────────────────────────────────────────────────── */}
      <section className="relative bg-charcoal overflow-hidden pt-[72px]">
        <div className="absolute inset-0 bg-gradient-to-br from-espresso via-charcoal to-ink opacity-95" />
        <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
          <p className="font-body text-xs tracking-[0.2em] uppercase text-bronze mb-4">Contact</p>
          <h1 className="font-display text-5xl sm:text-6xl lg:text-7xl text-ivory font-light leading-[1.05] max-w-2xl">
            Let&apos;s Start a Conversation
          </h1>
          <p className="font-body text-base text-ivory/60 mt-6 max-w-lg leading-relaxed">
            We&apos;d love to hear about your vision. Reach out and let&apos;s make something
            beautiful together.
          </p>
        </div>
      </section>

      {/* ── Main content ─────────────────────────────────────────────────── */}
      <section className="bg-ivory py-20 lg:py-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-5 gap-16 lg:gap-20 items-start">

            {/* Contact Info — left 2/5 */}
            <div className="lg:col-span-2 space-y-0">
              <h2 className="font-display text-3xl text-charcoal font-light mb-10">
                Find Us
              </h2>

              <div className="space-y-0 divide-y divide-cream">
                {email && (
                  <div className="flex items-start gap-5 py-6">
                    <div className="w-9 h-9 bg-sand flex items-center justify-center shrink-0 mt-0.5">
                      <Mail size={16} className="text-bronze" />
                    </div>
                    <div>
                      <p className="font-body text-xs tracking-[0.12em] uppercase text-taupe mb-1.5">Email</p>
                      <a
                        href={`mailto:${email}`}
                        className="font-display text-lg text-charcoal hover:text-bronze transition-colors duration-150 break-all"
                      >
                        {email}
                      </a>
                    </div>
                  </div>
                )}

                {phone && (
                  <div className="flex items-start gap-5 py-6">
                    <div className="w-9 h-9 bg-sand flex items-center justify-center shrink-0 mt-0.5">
                      <Phone size={16} className="text-bronze" />
                    </div>
                    <div>
                      <p className="font-body text-xs tracking-[0.12em] uppercase text-taupe mb-1.5">Phone</p>
                      <a
                        href={`tel:${phone}`}
                        className="font-display text-lg text-charcoal hover:text-bronze transition-colors duration-150"
                      >
                        {phone}
                      </a>
                    </div>
                  </div>
                )}

                {address && (
                  <div className="flex items-start gap-5 py-6">
                    <div className="w-9 h-9 bg-sand flex items-center justify-center shrink-0 mt-0.5">
                      <MapPin size={16} className="text-bronze" />
                    </div>
                    <div>
                      <p className="font-body text-xs tracking-[0.12em] uppercase text-taupe mb-1.5">Location</p>
                      <p className="font-display text-lg text-charcoal whitespace-pre-line">{address}</p>
                    </div>
                  </div>
                )}

                <div className="flex items-start gap-5 py-6">
                  <div className="w-9 h-9 bg-sand flex items-center justify-center shrink-0 mt-0.5">
                    <Clock size={16} className="text-bronze" />
                  </div>
                  <div>
                    <p className="font-body text-xs tracking-[0.12em] uppercase text-taupe mb-1.5">Studio Hours</p>
                    <p className="font-display text-lg text-charcoal">Mon – Thu: 10 AM – 5 PM</p>
                    <p className="font-body text-xs text-taupe mt-1">Available for sessions on weekends</p>
                  </div>
                </div>

                <div className="flex items-start gap-5 py-6">
                  <div className="w-9 h-9 bg-sand flex items-center justify-center shrink-0 mt-0.5">
                    <MessageSquare size={16} className="text-bronze" />
                  </div>
                  <div>
                    <p className="font-body text-xs tracking-[0.12em] uppercase text-taupe mb-2">Prefer to book directly?</p>
                    <a
                      href="/booking"
                      className="inline-flex items-center gap-1.5 font-body text-sm text-bronze border-b border-bronze pb-0.5 hover:text-bronze-dark hover:border-bronze-dark transition-colors duration-150"
                    >
                      <Calendar size={14} />
                      Request availability
                    </a>
                  </div>
                </div>
              </div>
            </div>

            {/* Contact Form — right 3/5 */}
            <div className="lg:col-span-3">
              <h2 className="font-display text-3xl text-charcoal font-light mb-10">
                Send a Message
              </h2>

              <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-6">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div>
                    <label htmlFor="name" className={labelClass}>
                      Name <span className="text-bronze normal-case tracking-normal">*</span>
                    </label>
                    <input
                      id="name"
                      type="text"
                      {...register('name')}
                      className={inputClass}
                      placeholder="Your full name"
                    />
                    {errors.name && (
                      <p className="mt-1.5 font-body text-xs text-red-600">{errors.name.message}</p>
                    )}
                  </div>

                  <div>
                    <label htmlFor="email" className={labelClass}>
                      Email <span className="text-bronze normal-case tracking-normal">*</span>
                    </label>
                    <input
                      id="email"
                      type="email"
                      {...register('email')}
                      className={inputClass}
                      placeholder="your@email.com"
                    />
                    {errors.email && (
                      <p className="mt-1.5 font-body text-xs text-red-600">{errors.email.message}</p>
                    )}
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div>
                    <label htmlFor="phone" className={labelClass}>Phone</label>
                    <input
                      id="phone"
                      type="tel"
                      {...register('phone')}
                      className={inputClass}
                      placeholder="Optional"
                    />
                  </div>

                  <div>
                    <label htmlFor="subject" className={labelClass}>Subject</label>
                    <input
                      id="subject"
                      type="text"
                      {...register('subject')}
                      className={inputClass}
                      placeholder="How can we help?"
                    />
                  </div>
                </div>

                <div>
                  <label htmlFor="message" className={labelClass}>
                    Message <span className="text-bronze normal-case tracking-normal">*</span>
                  </label>
                  <textarea
                    id="message"
                    rows={7}
                    {...register('message')}
                    className={inputClass + ' resize-none'}
                    placeholder="Tell us about your project, date, or any questions you have…"
                  />
                  {errors.message && (
                    <p className="mt-1.5 font-body text-xs text-red-600">{errors.message.message}</p>
                  )}
                </div>

                <button
                  type="submit"
                  disabled={isPending}
                  className="w-full sm:w-auto px-12 py-4 bg-bronze text-ivory font-body text-xs tracking-[0.18em] uppercase hover:bg-bronze-dark transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {isPending ? 'Sending…' : 'Send Message'}
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>

      {/* ── Google Map ───────────────────────────────────────────────────── */}
      {mapEmbedUrl && (
        <section className="bg-sand">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h2 className="font-display text-3xl text-charcoal font-light mb-8">Find Our Studio</h2>
          </div>
          <div className="relative w-full overflow-hidden" style={{ paddingTop: '38%', minHeight: '320px' }}>
            <iframe
              title="Mesh Photography studio location"
              src={mapEmbedUrl}
              className="absolute inset-0 w-full h-full border-0"
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              allowFullScreen
            />
          </div>
        </section>
      )}

      {/* Fallback map CTA when no embed URL is set */}
      {!mapEmbedUrl && address && (
        <section className="bg-charcoal py-16">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center gap-6 justify-between">
            <div>
              <p className="font-body text-xs tracking-widest uppercase text-bronze mb-2">Find Us</p>
              <p className="font-display text-2xl text-ivory font-light">{address}</p>
            </div>
            <a
              href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`}
              target="_blank"
              rel="noopener noreferrer"
              className="shrink-0 px-8 py-3 border border-ivory/40 text-ivory font-body text-xs tracking-widest uppercase hover:bg-ivory hover:text-charcoal transition-colors duration-150"
            >
              Open in Maps
            </a>
          </div>
        </section>
      )}
    </>
  );
}
