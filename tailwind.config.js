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
            },
            colors: {
                // Semantic brand colors
                primary: '#1a3490',
                secondary: '#525252',   // Neutral gray for secondary actions
                success: '#57d025',
                warning: '#f47612',
                danger: '#af1919',
                info: '#1a3490',        // Same as primary for informational

                // Neutrals
                white: '#ffffff',
                black: '#171717',
                'gray-light': '#a3a3a3',
                'gray-dark': '#525252',

                // UI semantic colors (for components)
                surface: '#f5f5f5',     // Subtle background for forms/sections
                border: '#d4d4d4',      // Default border color
                body: '#171717',        // Body text (same as black)
                subtle: '#525252',      // Subtle text (same as gray-dark)
                muted: '#a3a3a3',       // Muted elements (same as gray-light)

                // Legacy aliases (remove after migration)
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
