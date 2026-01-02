/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                cream: '#fdfbf7',
                gold: '#c5a059',
                anthracite: '#333333'
            }
        },
    },
    plugins: [],
}
