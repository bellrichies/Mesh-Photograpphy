import PageMeta from '@/components/ui/PageMeta';
import HeroCarousel from '@/components/public/HeroCarousel';
import BrandIntroSection from '@/components/public/BrandIntroSection';
import FeaturedPortfolioSection from '@/components/public/FeaturedPortfolioSection';
import ServicesTeaserSection from '@/components/public/ServicesTeaserSection';
import TestimonialsSection from '@/components/public/TestimonialsSection';
import BlogStoriesSection from '@/components/public/BlogStoriesSection';
import FinalCtaSection from '@/components/public/FinalCtaSection';
import { useHeroSlides } from '@/api/hero-slides';
import { useGalleryPhotos } from '@/api/galleries';
import { useTestimonials } from '@/api/testimonials';
import { useBlogPosts } from '@/api/blog';
import { usePublicSettings } from '@/api/settings';

export default function HomePage() {
  const { data: settings }                                      = usePublicSettings();
  const { data: slides,     isLoading: slidesLoading }          = useHeroSlides();
  const { data: photos,     isLoading: photosLoading }          = useGalleryPhotos(16);
  const { data: testimonials, isLoading: testimonialsLoading }  = useTestimonials();
  const { data: blogData,   isLoading: blogLoading }            = useBlogPosts({ per_page: 3 });

  return (
    <>
      <PageMeta
        title={settings?.seo.default_title ?? settings?.site.name}
        description={settings?.seo.default_description ?? undefined}
        siteName={settings?.site.name}
      />

      <HeroCarousel
        slides={slides ?? []}
        isLoading={slidesLoading}
        siteName={settings?.site.name}
      />

      <FeaturedPortfolioSection
        photos={photos ?? []}
        isLoading={photosLoading}
      />

      <BrandIntroSection settings={settings} photos={(photos ?? []).slice(0, 3)} />

      <ServicesTeaserSection />

      <TestimonialsSection
        testimonials={testimonials ?? []}
        isLoading={testimonialsLoading}
      />

      <BlogStoriesSection
        posts={blogData?.data ?? []}
        isLoading={blogLoading}
      />

      <FinalCtaSection />
    </>
  );
}
