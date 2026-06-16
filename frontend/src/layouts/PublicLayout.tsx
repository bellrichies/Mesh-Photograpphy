import { Outlet } from 'react-router-dom';
import Navbar from '@/components/layout/Navbar';
import Footer from '@/components/layout/Footer';
import FloatingWhatsAppButton from '@/components/layout/FloatingWhatsAppButton';
import { usePublicSettings } from '@/api/settings';
import { useTheme } from '@/hooks/useTheme';

export default function PublicLayout() {
  const { data: settings } = usePublicSettings();

  useTheme(settings?.theme);

  return (
    <>
      <Navbar settings={settings} />

      <main id="main-content">
        <Outlet />
      </main>

      <Footer settings={settings} />
      <FloatingWhatsAppButton settings={settings} />
    </>
  );
}
