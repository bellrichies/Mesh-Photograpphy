import { useState } from 'react';
import { Outlet, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '@/store/AuthContext';
import {
  LayoutDashboard, Images, FileText, Image, Layers, Star,
  SlidersHorizontal, MessageSquare, Calendar, Settings, Users,
  Menu, X, LogOut, ChevronRight,
} from 'lucide-react';
import { cn } from '@/utils/cn';

interface NavItem {
  label: string;
  to: string;
  icon: React.ReactNode;
  permission?: string;
}

const NAV_ITEMS: NavItem[] = [
  { label: 'Dashboard', to: '/admin', icon: <LayoutDashboard size={18} /> },
  { label: 'Galleries', to: '/admin/galleries', icon: <Images size={18} />, permission: 'manage-galleries' },
  { label: 'Blog', to: '/admin/blog', icon: <FileText size={18} />, permission: 'manage-blog' },
  { label: 'Media', to: '/admin/media', icon: <Image size={18} />, permission: 'manage-media' },
  { label: 'Pages', to: '/admin/pages', icon: <Layers size={18} />, permission: 'manage-pages' },
  { label: 'Services', to: '/admin/services', icon: <ChevronRight size={18} />, permission: 'manage-services' },
  { label: 'Testimonials', to: '/admin/testimonials', icon: <Star size={18} />, permission: 'manage-testimonials' },
  { label: 'Hero Slides', to: '/admin/hero-slides', icon: <SlidersHorizontal size={18} />, permission: 'manage-pages' },
  { label: 'Inquiries', to: '/admin/inquiries', icon: <MessageSquare size={18} />, permission: 'manage-inquiries' },
  { label: 'Bookings', to: '/admin/bookings', icon: <Calendar size={18} />, permission: 'manage-inquiries' },
  { label: 'Settings', to: '/admin/settings', icon: <Settings size={18} />, permission: 'manage-settings' },
  { label: 'Users', to: '/admin/users', icon: <Users size={18} />, permission: 'manage-users' },
];

export default function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/admin/login', { replace: true });
  };

  const visibleItems = NAV_ITEMS.filter(
    (item) => !item.permission || user?.permissions?.includes(item.permission)
  );

  const Sidebar = () => (
    <aside
      className={cn(
        'fixed inset-y-0 left-0 z-40 w-64 bg-espresso text-ivory flex flex-col transition-transform duration-200',
        'lg:relative lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      )}
    >
      <div className="h-16 flex items-center justify-between px-6 border-b border-white/10">
        <span className="font-display text-lg tracking-wide">Mesh Admin</span>
        <button
          className="lg:hidden text-ivory/60 hover:text-ivory"
          onClick={() => setSidebarOpen(false)}
          aria-label="Close sidebar"
        >
          <X size={20} />
        </button>
      </div>

      <nav className="flex-1 overflow-y-auto py-4" aria-label="Admin navigation">
        {visibleItems.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.to === '/admin'}
            className={({ isActive }) =>
              cn(
                'flex items-center gap-3 px-6 py-2.5 text-sm font-body transition-colors',
                isActive
                  ? 'bg-white/10 text-ivory'
                  : 'text-ivory/60 hover:bg-white/5 hover:text-ivory'
              )
            }
          >
            {item.icon}
            {item.label}
          </NavLink>
        ))}
      </nav>

      <div className="border-t border-white/10 p-4">
        <div className="text-xs text-ivory/40 font-body mb-2">
          {user?.first_name} {user?.last_name}
        </div>
        <button
          onClick={handleLogout}
          className="flex items-center gap-2 text-sm text-ivory/60 hover:text-ivory font-body transition-colors"
        >
          <LogOut size={16} />
          Sign out
        </button>
      </div>
    </aside>
  );

  return (
    <div className="flex h-screen bg-ivory-warm overflow-hidden">
      <Sidebar />

      {/* Backdrop for mobile */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-30 bg-black/50 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Topbar */}
        <header className="h-16 bg-white border-b border-cream flex items-center justify-between px-6 flex-shrink-0">
          <button
            className="lg:hidden text-taupe hover:text-charcoal"
            onClick={() => setSidebarOpen(true)}
            aria-label="Open sidebar"
          >
            <Menu size={22} />
          </button>
          <div className="ml-auto text-sm text-taupe font-body">
            {user?.email}
          </div>
        </header>

        <main className="flex-1 overflow-auto p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
