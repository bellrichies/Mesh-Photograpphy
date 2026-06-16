import WhatsAppIcon from '@/components/ui/WhatsAppIcon';
import { buildWhatsAppHref } from '@/utils/social-links';
import type { PublicSettings } from '@/types/models';

interface FloatingWhatsAppButtonProps {
  settings?: PublicSettings | null;
}

export default function FloatingWhatsAppButton({ settings }: FloatingWhatsAppButtonProps) {
  const href = buildWhatsAppHref(settings?.social.whatsapp);

  if (!href) return null;

  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      aria-label="Chat with us on WhatsApp"
      className="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40 flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-full border border-white/20 bg-[#25D366] text-white shadow-soft transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1ebe5d] hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366]"
    >
      <WhatsAppIcon className="h-6 w-6" />
    </a>
  );
}
