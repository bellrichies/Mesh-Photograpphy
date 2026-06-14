import { Outlet } from 'react-router-dom';

export default function AuthLayout() {
  return (
    <div className="min-h-screen bg-ivory-warm flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <h1 className="font-display text-3xl text-charcoal tracking-wide">Mesh Photography</h1>
          <p className="text-taupe text-sm mt-1 font-body">Content Management</p>
        </div>
        <Outlet />
      </div>
    </div>
  );
}
