import { ArrowLeft } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import type { ReactNode } from 'react';

interface Props {
  title: string;
  subtitle?: string;
  backTo?: string;
  actions?: ReactNode;
}

export default function PageHeader({ title, subtitle, backTo, actions }: Props) {
  const navigate = useNavigate();

  return (
    <div className="flex items-start justify-between mb-6">
      <div>
        {backTo && (
          <button
            onClick={() => navigate(backTo)}
            className="flex items-center gap-1.5 text-sm text-taupe hover:text-charcoal font-body mb-2 transition-colors"
          >
            <ArrowLeft size={14} />
            Back
          </button>
        )}
        <h1 className="font-display text-3xl text-charcoal">{title}</h1>
        {subtitle && (
          <p className="text-taupe font-body text-sm mt-1">{subtitle}</p>
        )}
      </div>
      {actions && <div className="flex items-center gap-3 ml-4">{actions}</div>}
    </div>
  );
}
