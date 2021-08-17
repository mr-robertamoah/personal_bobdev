module.exports = {
  purge: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      minWidth: {
        '0': '0',
        '1/4': '25%',
        '1/2': '50%',
        '3/4': '75%',
        'full': '100%',
        'screen': '100vw',
        'content': 'fit-content',
        '600': '600px',
        '400': '400px',
      },
      maxWidth: {
        '0': '0',
        '1/4': '25%',
        '1/2': '50%',
        '3/4': '75%',
        'full': '100%',
        'screen': '100vh',
        'content': 'fit-content',
      },
      minHeight: {
        '0': '0',
        '1/4': '25%',
        '1/2': '50%',
        '3/4': '75%',
        'full': '100%',
        'screen': '100vh',
        'content': 'fit-content',
        '600': '600px',
      },
      maxHeight: {
        '0': '0',
        '1/4': '25%',
        '1/2': '50%',
        '3/4': '75%',
        'full': '100%',
        'screen': '100vh',
        'content': 'fit-content',
        '600': '600px',
      },
      height: {
        "90vh": "90vh",
        'content': 'fit-content',
      },
      transitionProperty: {
        'height': 'height',
        'width': 'width',
        'visibility': 'visibility',
      }
    },
  },
  variants: {
    extend: {
      translate: ['group-hover'],
      visibility: ['group-hover'],
    },
  },
  plugins: [],
}
