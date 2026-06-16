import { useState, useCallback, useEffect } from 'react';

const prefersReducedMotion = () =>
  typeof window !== 'undefined' &&
  window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function useScrollReveal<T extends Element = HTMLElement>(threshold = 0.15) {
  const [node, setNode] = useState<T | null>(null);
  // When the user prefers reduced motion, content is shown immediately and the
  // CSS reduced-motion guard strips the transition — no scroll dependency.
  const [isVisible, setIsVisible] = useState(prefersReducedMotion);

  // Callback ref: called by React whenever the DOM node mounts or unmounts,
  // which triggers the effect below — unlike useRef, which never changes identity.
  const ref = useCallback((el: T | null) => setNode(el), []);

  useEffect(() => {
    if (!node || isVisible) return;

    // If the element is already in the viewport on mount, show it immediately.
    const rect = node.getBoundingClientRect();
    if (rect.top < window.innerHeight && rect.bottom >= 0) {
      setIsVisible(true);
      return;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
          observer.disconnect();
        }
      },
      // Trigger a little before the section is fully on-screen for a smoother feel.
      { threshold, rootMargin: '0px 0px -10% 0px' }
    );
    observer.observe(node);
    return () => observer.disconnect();
  }, [node, threshold, isVisible]);

  return { ref, isVisible };
}
