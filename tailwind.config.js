/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],


  
  theme: {
    extend: {
      fontFamily: {
        aerospace: ['Aerospace', 'sans-serif'],
      },
    },
    fontWeight: {
    normal: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
    extrabold: 800,
  }
  },
  plugins: [],
}
