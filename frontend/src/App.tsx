import { lazy, Suspense } from 'react';
import { Routes, Route } from 'react-router-dom';
import PublicLayout from './layouts/PublicLayout';
import AdminLayout from './layouts/AdminLayout';
import AuthLayout from './layouts/AuthLayout';
import PrivateRoute from './components/auth/PrivateRoute';

function PageSpinner() {
  return (
    <div className="min-h-screen bg-ivory flex items-center justify-center">
      <div className="w-8 h-8 border-2 border-bronze border-t-transparent rounded-full animate-spin" />
    </div>
  );
}

// Auth pages
const LoginPage          = lazy(() => import('./pages/auth/LoginPage'));
const ForgotPasswordPage = lazy(() => import('./pages/auth/ForgotPasswordPage'));
const ResetPasswordPage  = lazy(() => import('./pages/auth/ResetPasswordPage'));

// Public pages
const HomePage          = lazy(() => import('./pages/public/HomePage'));
const PortfolioPage     = lazy(() => import('./pages/public/PortfolioPage'));
const GalleryDetailPage = lazy(() => import('./pages/public/GalleryDetailPage'));
const ServicesPage      = lazy(() => import('./pages/public/ServicesPage'));
const ServiceDetailPage = lazy(() => import('./pages/public/ServiceDetailPage'));
const BlogPage          = lazy(() => import('./pages/public/BlogPage'));
const BlogPostPage      = lazy(() => import('./pages/public/BlogPostPage'));
const ContactPage       = lazy(() => import('./pages/public/ContactPage'));
const BookingPage       = lazy(() => import('./pages/public/BookingPage'));
const AboutPage         = lazy(() => import('./pages/public/AboutPage'));
const CmsPage           = lazy(() => import('./pages/public/CmsPage'));
const NotFoundPage      = lazy(() => import('./pages/public/NotFoundPage'));

// Admin pages
const DashboardPage       = lazy(() => import('./pages/admin/DashboardPage'));
const GalleriesPage       = lazy(() => import('./pages/admin/GalleriesPage'));
const GalleryFormPage     = lazy(() => import('./pages/admin/GalleryFormPage'));
const MediaPage           = lazy(() => import('./pages/admin/MediaPage'));
const BlogPostsPage       = lazy(() => import('./pages/admin/BlogPostsPage'));
const BlogPostFormPage    = lazy(() => import('./pages/admin/BlogPostFormPage'));
const ServicesAdminPage   = lazy(() => import('./pages/admin/ServicesPage'));
const ServiceFormPage     = lazy(() => import('./pages/admin/ServiceFormPage'));
const TestimonialsPage    = lazy(() => import('./pages/admin/TestimonialsPage'));
const HeroSlidesPage      = lazy(() => import('./pages/admin/HeroSlidesPage'));
const PagesAdminPage      = lazy(() => import('./pages/admin/PagesPage'));
const InquiriesPage       = lazy(() => import('./pages/admin/InquiriesPage'));
const InquiryDetailPage   = lazy(() => import('./pages/admin/InquiryDetailPage'));
const BookingsPage        = lazy(() => import('./pages/admin/BookingsPage'));
const BookingDetailPage   = lazy(() => import('./pages/admin/BookingDetailPage'));
const SettingsPage        = lazy(() => import('./pages/admin/SettingsPage'));
const UsersPage           = lazy(() => import('./pages/admin/UsersPage'));
const ActivityLogPage     = lazy(() => import('./pages/admin/ActivityLogPage'));

export default function App() {
  return (
    <Suspense fallback={<PageSpinner />}>
      <Routes>
        {/* Auth routes */}
        <Route element={<AuthLayout />}>
          <Route path="/admin/login"           element={<LoginPage />} />
          <Route path="/admin/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/admin/reset-password"  element={<ResetPasswordPage />} />
        </Route>

        {/* Admin routes — protected */}
        <Route
          element={
            <PrivateRoute>
              <AdminLayout />
            </PrivateRoute>
          }
        >
          <Route path="/admin"                        element={<DashboardPage />} />

          <Route path="/admin/galleries"              element={<GalleriesPage />} />
          <Route path="/admin/galleries/new"          element={<GalleryFormPage />} />
          <Route path="/admin/galleries/:id/edit"     element={<GalleryFormPage />} />

          <Route path="/admin/blog"                   element={<BlogPostsPage />} />
          <Route path="/admin/blog/new"               element={<BlogPostFormPage />} />
          <Route path="/admin/blog/:id/edit"          element={<BlogPostFormPage />} />

          <Route path="/admin/media"                  element={<MediaPage />} />

          <Route path="/admin/pages"                  element={<PagesAdminPage />} />

          <Route path="/admin/services"               element={<ServicesAdminPage />} />
          <Route path="/admin/services/new"           element={<ServiceFormPage />} />
          <Route path="/admin/services/:id/edit"      element={<ServiceFormPage />} />

          <Route path="/admin/testimonials"           element={<TestimonialsPage />} />
          <Route path="/admin/hero-slides"            element={<HeroSlidesPage />} />

          <Route path="/admin/inquiries"              element={<InquiriesPage />} />
          <Route path="/admin/inquiries/:id"          element={<InquiryDetailPage />} />

          <Route path="/admin/bookings"               element={<BookingsPage />} />
          <Route path="/admin/bookings/:id"           element={<BookingDetailPage />} />

          <Route path="/admin/settings"               element={<SettingsPage />} />
          <Route path="/admin/users"                  element={<UsersPage />} />
          <Route path="/admin/activity-log"           element={<ActivityLogPage />} />
        </Route>

        {/* Public routes */}
        <Route element={<PublicLayout />}>
          <Route path="/"                       element={<HomePage />} />
          <Route path="/portfolio"              element={<PortfolioPage />} />
          <Route path="/portfolio/:slug"        element={<GalleryDetailPage />} />
          <Route path="/services"               element={<ServicesPage />} />
          <Route path="/services/:slug"         element={<ServiceDetailPage />} />
          <Route path="/blog"                   element={<BlogPage />} />
          <Route path="/blog/:slug"             element={<BlogPostPage />} />
          <Route path="/contact"                element={<ContactPage />} />
          <Route path="/booking"                element={<BookingPage />} />
          <Route path="/about"                  element={<AboutPage />} />
          <Route path="/:slug"                  element={<CmsPage />} />
        </Route>

        {/* 404 */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  );
}
