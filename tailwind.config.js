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
                // Primary Green Theme (VotigoPro logo)
                primary: {
                    50: '#e8f5ee',
                    100: '#d4f0e0',
                    200: '#b8e6cc',
                    300: '#82d4a8',
                    400: '#4cbe7e',
                    500: '#2eaa5e',  // sidebar links
                    600: '#0d7a3e',  // main green, nav bar
                    700: '#0a6632',  // hover states
                    800: '#0d6e38',  // sidebar, buttons
                    900: '#064e2b',  // header
                    950: '#043a20',
                },
                // Secondary Amber/Orange Theme (VotigoPro logo bars)
                secondary: {
                    50: '#fef9e7',
                    100: '#fdefc4',
                    200: '#fce08d',
                    300: '#f9c846',
                    400: '#f7b731',
                    500: '#f39c12',
                    600: '#f39c12',  // orange buttons
                    700: '#d68910',
                    800: '#b7760e',
                    900: '#8a5a0a',
                    950: '#5c3c07',
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
