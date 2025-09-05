import preset from './vendor/filament/filament/tailwind.config.js'

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                danger: '#990f0c', // Rojo de Gadier
                gray: {
                    50: '#F2F2F2',
                    100: '#E6E6E6',
                    200: '#CCCCCC',
                    300: '#B3B3B3',
                    400: '#999999',
                    500: '#808080',
                    600: '#666666',
                    700: '#4D4D4D',
                    800: '#333333',
                    900: '#1A1A1A',
                    950: '#000000',
                },
                primary: '#990f0c',   // Rojo corporativo
                info: '#217482',      // Azul info
                success: '#20653d',   // Verde éxito
                warning: '#b78c2e',   // Amarillo alerta
                white: '#FFFFFF',
            },
            fontFamily: {
                sans: ['Instrument Sans', 'sans-serif'],
            },
            keyframes: {
                'fade-slide-up': {
                    '0%': { opacity: '0', transform: 'translateY(20px) scale(0.95)' },
                    '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'pulse-slow': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '.6' },
                },
                'grow-bar': {
                    '0%': { width: '0%' },
                    '100%': { width: '100%' },
                },
            },
            animation: {
                'fade-slide-up': 'fade-slide-up 0.6s ease-out forwards',
                'fade-in-up': 'fade-in-up 0.6s ease-in-out forwards',
                'pulse-slow': 'pulse-slow 2.5s ease-in-out infinite',
                'grow-bar': 'grow-bar 1.2s ease-in-out forwards',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
}
