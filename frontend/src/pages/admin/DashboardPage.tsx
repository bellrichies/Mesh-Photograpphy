import { useAuth } from '@/store/AuthContext';

export default function DashboardPage() {
  const { user } = useAuth();

  return (
    <div>
      <h1 className="font-display text-3xl text-charcoal mb-2">Dashboard</h1>
      <p className="text-taupe font-body text-sm mb-8">
        Welcome back, {user?.first_name} {user?.last_name}
      </p>
      <div className="bg-white rounded-xl border border-cream p-8 text-center">
        <p className="text-taupe font-body">
          Phase 2 dashboard metrics coming soon.
        </p>
      </div>
    </div>
  );
}
