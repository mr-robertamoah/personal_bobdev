/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      screens:{
        'sm': '480px'
      },
      minWidth: {
        '150': "150px",
      },
      width:{
        'content': 'fit-content'
      },
      height:{
        'content': 'fit-content'
      }
    },
  },
  plugins: [],
}
