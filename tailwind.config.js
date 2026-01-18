import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Arial', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary Blue Theme (from style_new.css)
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',  // sidebar links
                    600: '#2563eb',  // main blue, nav bar
                    700: '#1d4ed8',  // hover states
                    800: '#1e40af',  // sidebar, buttons
                    900: '#1e3a8a',  // header
                    950: '#172554',
                },
                // Secondary Orange Theme (from style.css)
                secondary: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ff6600',  // orange buttons
                    700: '#ea580c',
                    800: '#c2410c',
                    900: '#9a3412',
                    950: '#431407',
                },
                // Voting page colors (from Voting_Results.php)
                voting: {
                    header: '#2c3e50',
                    division: '#34495e',
                    results: '#ecf0f1',
                    border: '#bdc3c7',
                },
                // Place colors
                place: {
                    gold: '#FFD700',
                    silver: '#C0C0C0',
                    bronze: '#CD7F32',
                },
                // Status colors
                success: '#4CAF50',
                danger: '#dc2626',
                warning: '#f59e0b',
            },
            spacing: {
                'sidebar': '250px',
            },
            minHeight: {
                'screen-minus-header': 'calc(100vh - 60px)',
            },
        },
    },

    plugins: [forms],
};
