export function normalizeExternalUrl(url?: string | null): string | null {
  const value = (url ?? '').trim();
  if (!value) return null;
  if (/^https?:\/\//i.test(value)) return value;
  if (/^(www\.|[a-z0-9-]+\.[a-z]{2,})/i.test(value)) return `https://${value}`;
  return null;
}

export function buildWhatsAppHref(value?: string | null): string | null {
  const raw = (value ?? '').trim();
  if (!raw) return null;

  const directUrl = normalizeWhatsAppUrl(raw);
  if (directUrl) return directUrl;

  const phone = raw.replace(/[^\d+]/g, '');
  if (!/^\+?[1-9]\d{6,14}$/.test(phone)) return null;

  return `https://wa.me/${phone.replace(/[^\d]/g, '')}`;
}

function normalizeWhatsAppUrl(value: string): string | null {
  const withProtocol = /^https?:\/\//i.test(value) ? value : addProtocolForKnownHost(value);
  if (!withProtocol) return null;

  try {
    const url = new URL(withProtocol);
    const allowedHosts = new Set(['wa.me', 'api.whatsapp.com', 'web.whatsapp.com']);
    return allowedHosts.has(url.hostname.toLowerCase()) ? url.toString() : null;
  } catch {
    return null;
  }
}

function addProtocolForKnownHost(value: string): string | null {
  return /^(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)(\/|\?|$)/i.test(value)
    ? `https://${value}`
    : null;
}
