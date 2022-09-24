module.exports = {
    testRegex: 'D:/web projects/personal-bobdev/resources/js/tests/.*.spec.js$',
    moduleFileExtensions: [
        'js',
        'json',
        'vue'
    ],
    'transform': {
      ".*\\.(vue)$": "vue-jest",
      ".*\\.(js)$": "babel-jest"
    },
}