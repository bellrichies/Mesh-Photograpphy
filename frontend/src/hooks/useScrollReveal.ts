import { useState, useCallback, useEffect } from 'react';

export function useScrollReveal<T extends Element = HTMLElement>(threshold = 0.15) {
  const [node, setNode] = useState<T | null>(null);
  const [isVisible, setIsVisible] = useState(false);

  // Callback ref: called by React whenever the DOM node mounts or unmounts,
  // which triggers the effect below — unlike useRef, which never changes identity.
  const ref = useCallback((el: T | null) => setNode(el), []);

  useEffect(() => {
    if (!node) return;

    // If the element is already in the viewport on mount, show it immediately.
    const rect = node.getBoundingClientRect();
    if (rect.top < window.innerHeight && rect.bottom >= 0) {
      setIsVisible(true);
      return;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) setIsVisible(true);
      },
      { threshold }
    );
    observer.observe(node);
    return () => observer.disconnect();
  }, [node, threshold]);

  return { ref, isVisible };
}
