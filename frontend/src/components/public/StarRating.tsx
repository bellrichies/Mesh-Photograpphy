import { Star } from 'lucide-react';
import { cn } from '@/utils/cn';

interface StarRatingProps {
  rating: number;
  className?: string;
}

export default function StarRating({ rating, className }: StarRatingProps) {
  return (
    <div
      className={cn('flex gap-0.5 justify-center', className)}
      aria-label={`Rating: ${rating} out of 5 stars`}
    >
      {Array.from({ length: 5 }).map((_, i) => (
        <Star
          key={i}
          className={cn(
            'h-3 w-3',
            i < rating ? 'text-gold fill-gold' : 'text-cream fill-cream'
          )}
          aria-hidden="true"
        />
      ))}
    </div>
  );
}
