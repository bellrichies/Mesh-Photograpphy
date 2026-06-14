import { lazy, Suspense } from 'react';
import { Routes, Route } from 'react-router-dom';
import PublicLayout from './layouts/PublicLayout';
import AdminLayout from './layouts/AdminLayout';
import AuthLayout from './layouts/AuthLayout';
import PrivateRoute from './components/auth/PrivateRoute';

// Page spinner for Suspense boundaries
function PageSpinner() {
  return (
    <div className="min-h-screen bg-ivory flex items-center justify-center">
      <div className="w-8 h-8 border-2 border-bronze border-t-transparent rounded-full animate-spin" />
    </div>
  );
}

// Auth pages
const LoginPage = lazy(() => import('./pages/auth/LoginPage'));

// Public pages (Phase 2 pages added as stubs here)
const HomePage    = lazy(() => import('./pages/public/HomePage'));
const NotFoundPage = lazy(() => import('./pages/public/NotFoundPage'));

// Admin pages
const DashboardPage = lazy(() => import('./pages/admin/DashboardPage'));

export default function App() {
  return (
    <Suspense fallback={<PageSpinner />}>
      <Routes>
        {/* Auth routes */}
        <Route element={<AuthLayout />}>
          <Route path="/admin/login" element={<LoginPage />} />
        </Route>

        {/* Admin routes — protected */}
        <Route
          element={
            <PrivateRoute>
              <AdminLayout />
            </PrivateRoute>
          }
        >
          <Route path="/admin" element={<DashboardPage />} />
          <Route path="/admin/galleries" element={<DashboardPage />} />
          <Route path="/admin/blog" element={<DashboardPage />} />
          <Route path="/admin/media" element={<DashboardPage />} />
          <Route path="/admin/pages" element={<DashboardPage />} />
          <Route path="/admin/services" element={<DashboardPage />} />
          <Route path="/admin/testimonials" element={<DashboardPage />} />
          <Route path="/admin/hero-slides" element={<DashboardPage />} />
          <Route path="/admin/inquiries" element={<DashboardPage />} />
          <Route path="/admin/bookings" element={<DashboardPage />} />
          <Route path="/admin/settings" element={<DashboardPage />} />
          <Route path="/admin/users" element={<DashboardPage />} />
        </Route>

        {/* Public routes */}
        <Route element={<PublicLayout />}>
          <Route path="/" element={<HomePage />} />
        </Route>

        {/* 404 */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  );
}
