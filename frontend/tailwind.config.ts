import type { Config } from 'tailwindcss';

const config: Config = {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        charcoal:         '#1a1a1a',
        'charcoal-light': '#2d2d2d',
        ivory:            '#faf9f7',
        'ivory-warm':     '#f5f1eb',
        cream:            '#ebe5dc',
        bronze:           '#9a7b5c',
        'bronze-light':   '#b8956f',
        'bronze-dark':    '#7a5f42',
        gold:             '#c4a77d',
        taupe:            '#a8998a',
        sand:             '#f6f1ea',
        parchment:        '#f4efe8',
        pine:             '#2f4c45',
        moss:             '#3f5a4f',
        espresso:         '#1b1714',
        ink:              '#171411',
        ember:            '#b08968',
        clay:             '#a67f63',
      },
      fontFamily: {
        display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        body:    ['Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 10px 30px -16px rgba(15, 23, 42, 0.28)',
      },
      borderRadius: {
        '2xl': '1rem',
        '3xl': '1.5rem',
        '4xl': '2rem',
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '26': '6.5rem',
        '30': '7.5rem',
      },
    },
  },
  plugins: [],
};

export default config;
