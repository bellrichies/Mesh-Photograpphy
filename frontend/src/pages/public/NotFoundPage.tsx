import { Link } from 'react-router-dom';

export default function NotFoundPage() {
  return (
    <div className="min-h-screen bg-ivory flex items-center justify-center px-4">
      <div className="text-center">
        <p className="font-display text-8xl text-bronze font-light">404</p>
        <h1 className="font-display text-3xl text-charcoal mt-4 mb-2">Page Not Found</h1>
        <p className="font-body text-taupe mb-8">
          The page you're looking for doesn't exist.
        </p>
        <Link
          to="/"
          className="inline-block bg-bronze text-ivory font-body text-sm px-6 py-3 rounded-lg hover:bg-bronze-light transition-colors"
        >
          Return Home
        </Link>
      </div>
    </div>
  );
}
