const Encore = require('@symfony/webpack-encore');

Encore
    // Other configurations...
    .enableSassLoader()
    .enableReactPreset()
    .enableVueLoader()
    .enableStimulusBridge()
    .autoProvidejQuery()
    .addEntry('app', './assets/app.js')
    // Optionally, add FullCalendar as a dependency for easy import
    .enablePostCssLoader()
    .enableVersioning()
;

module.exports = Encore.getWebpackConfig();
