import { Outlet } from 'react-router-dom';
import Navbar from '@/components/layout/Navbar';
import Footer from '@/components/layout/Footer';
import { usePublicSettings } from '@/api/settings';

export default function PublicLayout() {
  const { data: settings } = usePublicSettings();

  return (
    <>
      <Navbar settings={settings} />

      <main id="main-content">
        <Outlet />
      </main>

      <Footer settings={settings} />
    </>
  );
}
