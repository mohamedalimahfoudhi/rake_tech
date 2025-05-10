<<<<<<< HEAD
<<<<<<< HEAD
const path = require('path');

module.exports = {
  mode: 'development',
  entry: './assets/app.js', // Your main JS file
  output: {
    path: path.resolve(__dirname, 'public/build'),
    filename: '[name].js',
    publicPath: '/build',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },
  resolve: {
    alias: {
      '@fullcalendar/core': path.resolve(__dirname, 'node_modules/@fullcalendar/core'),
      '@fullcalendar/daygrid': path.resolve(__dirname, 'node_modules/@fullcalendar/daygrid'),
      '@fullcalendar/interaction': path.resolve(__dirname, 'node_modules/@fullcalendar/interaction'),
    },
  },
  devtool: 'source-map',
};
=======
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
>>>>>>> origin/gest-materials
=======
const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js') // Main JS entry file
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })
    .enableSassLoader();

module.exports = Encore.getWebpackConfig();
>>>>>>> origin/gest-event
