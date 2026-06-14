import { useAdminDashboard } from '@/api/admin/dashboard';
import { useAuth } from '@/store/AuthContext';
import { Link } from 'react-router-dom';
import {
  Images, FileText, Image, Layers, MessageSquare, Calendar,
  SlidersHorizontal, Star, Settings2,
} from 'lucide-react';
import type { DashboardMetrics } from '@/types/models';

interface MetricCardProps {
  label: string;
  total: number;
  detail?: string;
  icon: React.ReactNode;
  href: string;
  accentColor?: string;
}

function MetricCard({ label, total, detail, icon, href, accentColor = 'bg-bronze' }: MetricCardProps) {
  return (
    <Link
      to={href}
      className="bg-white rounded-xl border border-cream p-5 hover:shadow-soft transition-shadow group"
    >
      <div className="flex items-start justify-between mb-4">
        <div className={`w-10 h-10 rounded-lg ${accentColor} flex items-center justify-center text-ivory`}>
          {icon}
        </div>
      </div>
      <div className="font-display text-3xl text-charcoal mb-0.5">{total}</div>
      <div className="text-sm font-body text-charcoal mb-0.5">{label}</div>
      {detail && <div className="text-xs font-body text-taupe">{detail}</div>}
    </Link>
  );
}

function MetricsSkeleton() {
  return (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      {Array.from({ length: 8 }).map((_, i) => (
        <div key={i} className="bg-white rounded-xl border border-cream p-5 animate-pulse">
          <div className="w-10 h-10 rounded-lg bg-cream mb-4" />
          <div className="h-8 bg-cream rounded w-12 mb-2" />
          <div className="h-4 bg-cream rounded w-24" />
        </div>
      ))}
    </div>
  );
}

function buildCards(m: DashboardMetrics): MetricCardProps[] {
  return [
    {
      label: 'Galleries',
      total: m.galleries.total,
      detail: `${m.galleries.published} published`,
      icon: <Images size={18} />,
      href: '/admin/galleries',
      accentColor: 'bg-bronze',
    },
    {
      label: 'Blog Posts',
      total: m.blog_posts.total,
      detail: `${m.blog_posts.published} published`,
      icon: <FileText size={18} />,
      href: '/admin/blog',
      accentColor: 'bg-espresso',
    },
    {
      label: 'Media Files',
      total: m.media.total,
      icon: <Image size={18} />,
      href: '/admin/media',
      accentColor: 'bg-taupe',
    },
    {
      label: 'CMS Pages',
      total: m.pages.total,
      detail: `${m.pages.published} published`,
      icon: <Layers size={18} />,
      href: '/admin/pages',
      accentColor: 'bg-pine',
    },
    {
      label: 'Services',
      total: m.services.total,
      detail: `${m.services.published} published`,
      icon: <Settings2 size={18} />,
      href: '/admin/services',
      accentColor: 'bg-clay',
    },
    {
      label: 'Testimonials',
      total: m.testimonials.total,
      detail: `${m.testimonials.published} published`,
      icon: <Star size={18} />,
      href: '/admin/testimonials',
      accentColor: 'bg-gold',
    },
    {
      label: 'Inquiries',
      total: m.inquiries.total,
      detail: `${m.inquiries.new} new`,
      icon: <MessageSquare size={18} />,
      href: '/admin/inquiries',
      accentColor: m.inquiries.new > 0 ? 'bg-blue-600' : 'bg-taupe',
    },
    {
      label: 'Bookings',
      total: m.bookings.total,
      detail: `${m.bookings.new} new`,
      icon: <Calendar size={18} />,
      href: '/admin/bookings',
      accentColor: m.bookings.new > 0 ? 'bg-blue-600' : 'bg-taupe',
    },
    {
      label: 'Hero Slides',
      total: m.hero_slides.total,
      detail: `${m.hero_slides.published} published`,
      icon: <SlidersHorizontal size={18} />,
      href: '/admin/hero-slides',
      accentColor: 'bg-ember',
    },
  ];
}

export default function DashboardPage() {
  const { user }               = useAuth();
  const { data, isLoading }    = useAdminDashboard();

  return (
    <div>
      <h1 className="font-display text-3xl text-charcoal mb-1">Dashboard</h1>
      <p className="text-taupe font-body text-sm mb-8">
        Welcome back, {user?.first_name} {user?.last_name}
      </p>

      {isLoading ? (
        <MetricsSkeleton />
      ) : data ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {buildCards(data).map((card) => (
            <MetricCard key={card.href} {...card} />
          ))}
        </div>
      ) : (
        <div className="bg-white rounded-xl border border-cream p-8 text-center">
          <p className="text-taupe font-body text-sm">Failed to load metrics.</p>
        </div>
      )}
    </div>
  );
}
