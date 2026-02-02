import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Logo font - load via Google Fonts in app.blade.php
                logo: ['Space Grotesk', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                /*
                 * Theme-aware colors (use CSS variables)
                 * These adapt when theme class changes on <html>
                 */
                primary: {
                    DEFAULT: 'rgb(var(--color-primary) / <alpha-value>)',
                    hover: 'rgb(var(--color-primary-hover) / <alpha-value>)',
                },
                success: 'rgb(var(--color-success) / <alpha-value>)',
                warning: 'rgb(var(--color-warning) / <alpha-value>)',
                danger: 'rgb(var(--color-danger) / <alpha-value>)',
                info: 'rgb(var(--color-info) / <alpha-value>)',

                /*
                 * Dark mode surface colors
                 */
                bg: 'rgb(var(--color-bg) / <alpha-value>)',
                surface: {
                    DEFAULT: 'rgb(var(--color-surface) / <alpha-value>)',
                    elevated: 'rgb(var(--color-surface-elevated) / <alpha-value>)',
                    overlay: 'rgb(var(--color-surface-overlay) / <alpha-value>)',
                    inset: 'rgb(var(--color-surface-inset) / <alpha-value>)',
                    header: 'rgb(var(--color-surface-header) / <alpha-value>)',
                },

                /*
                 * Text colors
                 */
                body: 'rgb(var(--color-text) / <alpha-value>)',
                muted: 'rgb(var(--color-text-muted) / <alpha-value>)',
                subtle: 'rgb(var(--color-text-subtle) / <alpha-value>)',

                /*
                 * Border colors
                 */
                border: {
                    DEFAULT: 'rgb(var(--color-border) / <alpha-value>)',
                    strong: 'rgb(var(--color-border-strong) / <alpha-value>)',
                },

                /*
                 * Static colors (don't change with theme)
                 */
                white: '#ffffff',
                black: '#171717',
                'gray-light': '#a3a3a3',
                'gray-dark': '#525252',

                /*
                 * Secondary (neutral gray, theme-independent)
                 */
                secondary: '#525252',

                /*
                 * Legacy aliases (to be removed after migration)
                 */
                'propoff-red': '#af1919',
                'propoff-orange': '#f47612',
                'propoff-green': '#57d025',
                'propoff-dark-green': '#186916',
                'propoff-blue': '#1a3490',
            },
        },
    },

    plugins: [forms],
};
