const mix = require("laravel-mix");

mix.setPublicPath('public');
mix.setResourceRoot('../');

mix.js("resources/js/app.js", "js")
    .sass("resources/css/app.scss", "css", {
        sassOptions: {
            quietDeps: true,
        },
    })
    .options({
        postCss: [require("postcss-import"), require("tailwindcss")],
    })

if (mix.inProduction()) {
    mix.version();
}
