const HtmlWebpackPlugin = require('html-webpack-plugin')
const path = require('path')

module.exports = {
     output: {
       path: path.join(__dirname, '/dist'),
       filename: 'bundle.js'
     },
     devServer: {
       port: 3000,
       watchContentBase: true,
       headers: {
        'Access-Control-Allow-Headers': '*',
        'Access-Control-Allow-Methods': '*',
        'Access-Control-Allow-Origin': 'http://hostinger.dd'
      }
     },
  
     module: {
       rules: [
         {
           test: /\.(js|jsx)$/,
           exclude: /node_modules/,
           use: {
             loader: 'babel-loader'
           }
         },
         {
           test: /\.css$/,
           use: ['style-loader', 'css-loader']
         }
       ]
     },
     plugins: [new HtmlWebpackPlugin({ template: './src/index.html' })],
}