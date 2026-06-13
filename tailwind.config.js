const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
  content: [
    './app/**/*.php',
    './resources/views/**/*.php',
    './public/assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        charcoal: {
          DEFAULT: '#1a1a1a',
          light: '#2d2d2d',
          dark: '#0f0f0f',
        },
        ivory: {
          DEFAULT: '#faf9f7',
          warm: '#f5f1eb',
        },
        cream: '#ebe5dc',
        bronze: {
          DEFAULT: '#9a7b5c',
          light: '#b8956f',
          dark: '#7a5f42',
        },
        gold: '#c4a77d',
        stone: {
          DEFAULT: '#d4ccc0',
          ...defaultTheme.colors.stone,
        },
        taupe: '#a8998a',
        slate: {
          DEFAULT: '#6b6b6b',
          ...defaultTheme.colors.slate,
        },
        ember: '#b08968',
        sand: '#f6f1ea',
        ink: '#171411',
        clay: '#a67f63',
        pine: '#2f4c45',
        parchment: '#f4efe8',
        espresso: '#1b1714',
        moss: '#3f5a4f',
      },
      fontFamily: {
        display: ['Cormorant Garamond', 'Georgia', 'serif'],
        body: ['Inter', ...defaultTheme.fontFamily.sans],
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
