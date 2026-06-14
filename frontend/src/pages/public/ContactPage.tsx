import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import toast from 'react-hot-toast';
import { Mail, Phone, MapPin } from 'lucide-react';
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
  const mapEmbedUrl = (settings?.contact as Record<string, string | null | undefined> | undefined)?.map_embed_url ?? null;

  return (
    <>
      <PageMeta title="Contact" description="Get in touch with us. We'd love to hear from you." />

      <div className="pt-24 pb-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Header */}
          <div className="text-center mb-16">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Contact</p>
            <h1 className="font-display text-5xl text-charcoal font-light">Get in Touch</h1>
            <p className="font-body text-taupe mt-3 max-w-md mx-auto">
              We'd love to hear about your vision. Reach out and let's start the conversation.
            </p>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
            {/* Form */}
            <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-5">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <label htmlFor="name" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                    Name <span className="text-bronze">*</span>
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

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <label htmlFor="phone" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">Phone</label>
                  <input
                    id="phone"
                    type="tel"
                    {...register('phone')}
                    className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                    placeholder="Optional"
                  />
                </div>

                <div>
                  <label htmlFor="subject" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">Subject</label>
                  <input
                    id="subject"
                    type="text"
                    {...register('subject')}
                    className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                    placeholder="How can we help?"
                  />
                </div>
              </div>

              <div>
                <label htmlFor="message" className="block font-body text-xs tracking-wide text-charcoal mb-1.5">
                  Message <span className="text-bronze">*</span>
                </label>
                <textarea
                  id="message"
                  rows={6}
                  {...register('message')}
                  className="w-full border border-cream bg-ivory px-4 py-3 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg resize-none"
                  placeholder="Tell us about your project or ask any questions…"
                />
                {errors.message && <p className="mt-1 font-body text-xs text-red-600">{errors.message.message}</p>}
              </div>

              <button
                type="submit"
                disabled={isPending}
                className="w-full sm:w-auto px-10 py-3 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {isPending ? 'Sending…' : 'Send Message'}
              </button>
            </form>

            {/* Info */}
            <div className="space-y-8">
              {phone && (
                <div className="flex items-start gap-4">
                  <div className="w-10 h-10 rounded-full bg-ivory-warm flex items-center justify-center shrink-0">
                    <Phone size={18} className="text-bronze" />
                  </div>
                  <div>
                    <p className="font-body text-xs tracking-widest uppercase text-taupe mb-1">Phone</p>
                    <a href={`tel:${phone}`} className="font-display text-xl text-charcoal hover:text-bronze transition-colors">
                      {phone}
                    </a>
                  </div>
                </div>
              )}

              {email && (
                <div className="flex items-start gap-4">
                  <div className="w-10 h-10 rounded-full bg-ivory-warm flex items-center justify-center shrink-0">
                    <Mail size={18} className="text-bronze" />
                  </div>
                  <div>
                    <p className="font-body text-xs tracking-widest uppercase text-taupe mb-1">Email</p>
                    <a href={`mailto:${email}`} className="font-display text-xl text-charcoal hover:text-bronze transition-colors">
                      {email}
                    </a>
                  </div>
                </div>
              )}

              {address && (
                <div className="flex items-start gap-4">
                  <div className="w-10 h-10 rounded-full bg-ivory-warm flex items-center justify-center shrink-0">
                    <MapPin size={18} className="text-bronze" />
                  </div>
                  <div>
                    <p className="font-body text-xs tracking-widest uppercase text-taupe mb-1">Location</p>
                    <p className="font-display text-xl text-charcoal">{address}</p>
                  </div>
                </div>
              )}

              <div className="bg-ivory-warm rounded-2xl p-6 mt-8">
                <h3 className="font-display text-xl text-charcoal mb-2">Prefer to book directly?</h3>
                <p className="font-body text-sm text-taupe mb-4">Use our booking form to check availability and request a session.</p>
                <a
                  href="/booking"
                  className="inline-block px-6 py-2.5 border border-bronze text-bronze font-body text-xs tracking-widest uppercase hover:bg-bronze hover:text-ivory transition-colors"
                >
                  Book a Session
                </a>
              </div>
            </div>
          </div>

          {/* Google Map embed */}
          {mapEmbedUrl && (
            <div className="mt-16">
              <div className="relative w-full overflow-hidden rounded-xl" style={{ paddingTop: '40%', minHeight: '300px' }}>
                <iframe
                  title="Studio location map"
                  src={mapEmbedUrl}
                  className="absolute inset-0 w-full h-full border-0"
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  allowFullScreen
                />
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
