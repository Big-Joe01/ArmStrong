module.exports = {
  /** @type {import('tailwindcss').Config} */
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          light: '#4da6ff',
          DEFAULT: '#507EEF',
          dark: '#1E1B1C',
        },
        secondary: {
          light: '#f8f9fa',
          DEFAULT: '#EDEDED',
          dark: '#dee2e6',
        },
        armstrong: {
          blue: '#507EEF',
          dark: '#1E1B1C',
          gray: '#4A5568',
          lightgray: '#EDEDED',
          footerbg: '#1E1B1C',
          white: '#ffffff',
          accent: '#f2ce2c',
        },
      },
      fontFamily: {
        'graphik-medium': ['Graphik Medium', 'sans-serif'],
        'graphik-regular': ['Graphik Regular', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.6s ease-in-out',
        'slide-up': 'slideUp 0.6s ease-out',
        'slide-in-left': 'slideInLeft 0.8s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(30px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideInLeft: {
          '0%': { opacity: '0', transform: 'translateX(-40px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
      },
    },
  },
  plugins: [],
};
