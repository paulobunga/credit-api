const mix = require("laravel-mix");

mix.setPublicPath("public");
mix.setResourceRoot("../");

mix.copy('node_modules/animate.css/animate.min.css', 'public/css');

mix.js("resources/js/app.js", "js")
    .postCss("resources/css/app.css", "css", [
        require("tailwindcss"),
        require('autoprefixer'),
    ]);

if (mix.inProduction()) {
    mix.version();
}
