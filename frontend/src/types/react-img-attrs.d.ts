// React 18.3 runtime does not recognise camelCase `fetchPriority` on <img>.
// The correct lowercase HTML attribute must be used instead.
// This augmentation adds it to the JSX types so TypeScript stays happy.
import 'react';

declare module 'react' {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface ImgHTMLAttributes<T> {
    fetchpriority?: 'high' | 'low' | 'auto';
  }
}
