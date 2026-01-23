import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
    theme: {
        extend: {
            colors: {
                // Colores Corporativos Anica
                primary: {
                    DEFAULT: '#F15A24', // Naranja principal
                    light: '#FF7A47',
                    dark: '#D14A1A',
                },
                secondary: {
                    DEFAULT: '#3EA530', // Verde vibrante
                    light: '#5BC24A',
                    dark: '#2E8523',
                },
                accent: {
                    DEFAULT: '#67C2EC', // Azul cielo
                    light: '#8DD4F3',
                    dark: '#4AA8D8',
                },
                premium: {
                    DEFAULT: '#650E34', // Morado oscuro
                    light: '#8A1447',
                    dark: '#4A0A27',
                },
                neutral: {
                    DEFAULT: '#333333', // Gris oscuro
                    light: '#515151',
                    lighter: '#6B6B6B',
                },
            },
            fontFamily: {
                lobster: ['Lobster', 'cursive'],
                montserrat: ['Montserrat', 'sans-serif'],
            },
        },
    },
    plugins: [
        typography,
    ],
};
