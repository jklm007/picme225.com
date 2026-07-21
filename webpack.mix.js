const mix = require('laravel-mix');

mix.js('resources/assets/js/app.js', 'public/js')
   .vue({ version: 2 })
   .sass('resources/assets/sass/app.scss', 'public/css')
   .options({ processCssUrls: false });
