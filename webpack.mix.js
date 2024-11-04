let mix = require('laravel-mix');
let BrowserSyncPlugin = require('browser-sync-webpack-plugin');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

mix.webpackConfig({
    plugins: [
        new BrowserSyncPlugin({
            host: 'localhost',
            port: 3000,
            proxy: 'your-local-dev-url.test',
            files: [
                'app/**/*.php',
                'resources/views/**/*.php',
                'resources/js/**/*.vue',
                'resources/css/**/*.css',
                'public/js/**/*.js',
                'public/css/**/*.css'
            ]
        })
    ]
});
