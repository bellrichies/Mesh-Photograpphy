import { useAdminDashboard } from '@/api/admin/dashboard';
import { useActivityLogs, type ActivityLogEntry } from '@/api/admin/activity-logs';
import { useAuth } from '@/store/AuthContext';
import { Link } from 'react-router-dom';
import {
  Images, FileText, Image, Layers, MessageSquare, Calendar,
  SlidersHorizontal, Star, Settings2, ClipboardList, Mail,
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
      label: 'Subscribers',
      total: m.subscribers.total,
      detail: `${m.subscribers.active} active`,
      icon: <Mail size={18} />,
      href: '/admin/newsletter/subscribers',
      accentColor: 'bg-pine',
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

function formatActivityTime(value: string): string {
  return new Date(value).toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

function RecentActivity({ activities, isLoading }: { activities: ActivityLogEntry[]; isLoading: boolean }) {
  return (
    <section className="mt-8 bg-white rounded-xl border border-cream">
      <div className="flex items-center justify-between px-5 py-4 border-b border-cream">
        <div className="flex items-center gap-2">
          <ClipboardList size={18} className="text-bronze" />
          <h2 className="font-display text-xl text-charcoal">Recent Activity</h2>
        </div>
        <Link to="/admin/activity-log" className="text-xs font-body text-bronze hover:text-bronze-dark">
          View all
        </Link>
      </div>

      {isLoading ? (
        <div className="p-5 space-y-4">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="animate-pulse">
              <div className="h-4 bg-cream rounded w-2/3 mb-2" />
              <div className="h-3 bg-cream rounded w-1/3" />
            </div>
          ))}
        </div>
      ) : activities.length > 0 ? (
        <ul className="divide-y divide-cream">
          {activities.map((activity) => (
            <li key={activity.id} className="px-5 py-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
              <div className="min-w-0">
                <p className="font-body text-sm text-charcoal">
                  {activity.description ?? `${activity.action} ${activity.model_type ?? 'resource'}`}
                </p>
                <p className="font-body text-xs text-taupe mt-1">
                  {activity.user ? `${activity.user.name} (${activity.user.email})` : 'System'}
                  {activity.model_type ? ` - ${activity.model_type}${activity.model_id ? ` #${activity.model_id}` : ''}` : ''}
                </p>
              </div>
              <time className="font-body text-xs text-taupe shrink-0" dateTime={activity.created_at}>
                {formatActivityTime(activity.created_at)}
              </time>
            </li>
          ))}
        </ul>
      ) : (
        <div className="px-5 py-10 text-center">
          <p className="font-body text-sm text-taupe">No activity recorded yet.</p>
        </div>
      )}
    </section>
  );
}

export default function DashboardPage() {
  const { user }               = useAuth();
  const { data, isLoading }    = useAdminDashboard();
  const { data: activityData, isLoading: activityLoading } = useActivityLogs({ page: 1, per_page: 6 });

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

      <RecentActivity activities={activityData?.data ?? []} isLoading={activityLoading} />
    </div>
  );
}
