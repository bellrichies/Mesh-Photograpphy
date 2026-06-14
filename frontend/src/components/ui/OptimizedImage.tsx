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
  srcSet?: string;
  sizes?: string;
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
  srcSet,
  sizes,
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
      srcSet={srcSet}
      sizes={sizes ?? (srcSet ? '(max-width: 640px) 320px, (max-width: 1280px) 640px, 1280px' : undefined)}
      className={cn('object-' + objectFit, className)}
    />
  );
}
