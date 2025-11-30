import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // ចាំបាច់
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // ប្រើ rgb(var(...) / <alpha-value>) ដើម្បីឱ្យ Tailwind ស្គាល់ Opacity
                primary: 'rgb(var(--color-primary) / <alpha-value>)',
                secondary: 'rgb(var(--color-secondary) / <alpha-value>)',
                'sidebar-bg': 'rgb(var(--sidebar-bg) / <alpha-value>)',
                'sidebar-text': 'rgb(var(--sidebar-text) / <alpha-value>)',
                'header-bg': 'rgb(var(--header-bg) / <alpha-value>)',
                'page-bg': 'rgb(var(--page-bg) / <alpha-value>)',
                'card-bg': 'rgb(var(--card-bg) / <alpha-value>)',
                'input-bg': 'rgb(var(--input-bg) / <alpha-value>)',
                'border-color': 'rgb(var(--custom-border) / <alpha-value>)',
            },
            boxShadow: {
                'custom': 'var(--custom-shadow)',
            },
        },
    },
    plugins: [forms],
};