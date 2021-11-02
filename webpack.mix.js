const mix = require('laravel-mix');
// require('laravel-mix-alias');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.ts('resources/js/app.ts', 'public/js')
    .vue({
        globalStyles: 'resources/sass/app.scss',
        version: 3
    })
    .postCss('resources/css/app.css', 'public/css', [
        require("tailwindcss"),
    ])
    .sass('resources/sass/app.scss', 'public/css');
    // .alias({
    //     '@': '/resources/js',
    //     '@components': '/resources/js/components',
    // });