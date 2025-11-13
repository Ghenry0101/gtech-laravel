import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        borderColor: theme => ({
            ...theme('colors'),
            DEFAULT: '#EAEAEA',
        }),
        rounded: {
            'none': '0',
            'sm': '.125rem',
            'md': '.375rem',
            'lg': '.5rem',
            'xl': '.75rem',
            '2xl': '1rem',
            '3xl': '1.5rem',
            'full': '9999px',
        },
        extend: {
            fontFamily: {
                // Use the Rubik Mono One Google Font everywhere font-sans is applied
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
