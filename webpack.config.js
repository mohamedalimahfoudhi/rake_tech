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
