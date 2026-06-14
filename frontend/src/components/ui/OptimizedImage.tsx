import { cn } from '@/utils/cn';

interface OptimizedImageProps {
  src: string;
  alt: string;
  width?: number;
  height?: number;
  className?: string;
  loading?: 'lazy' | 'eager';
  fetchPriority?: 'high' | 'low' | 'auto';
  objectFit?: 'cover' | 'contain' | 'fill';
}

export default function OptimizedImage({
  src,
  alt,
  width,
  height,
  className,
  loading = 'lazy',
  fetchPriority = 'auto',
  objectFit = 'cover',
}: OptimizedImageProps) {
  return (
    <img
      src={src}
      alt={alt}
      width={width}
      height={height}
      loading={loading}
      fetchPriority={fetchPriority}
      decoding="async"
      className={cn('object-' + objectFit, className)}
    />
  );
}
