import type { SVGProps } from 'react';

export default function WhatsAppIcon(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" {...props}>
      <path
        d="M4.2 19.8l1.1-3.7A8 8 0 1 1 8 18.7l-3.8 1.1Z"
        stroke="currentColor"
        strokeWidth="1.8"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M9.2 8.2c.2-.4.4-.5.8-.5h.6c.2 0 .4.1.5.4l.7 1.6c.1.3.1.5-.1.7l-.4.5c-.1.2-.1.4 0 .6.5.9 1.2 1.6 2.1 2.1.2.1.4.1.6 0l.5-.4c.2-.2.5-.2.7-.1l1.6.7c.3.1.4.3.4.6v.6c0 .4-.2.6-.5.8-.4.2-.9.3-1.5.3-3.8 0-6.9-3.1-6.9-6.9 0-.6.1-1.1.3-1.5Z"
        fill="currentColor"
      />
    </svg>
  );
}
