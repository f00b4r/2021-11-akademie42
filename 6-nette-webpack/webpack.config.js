const path = require('path');
const {merge} = require('webpack-merge');
const {VueLoaderPlugin} = require('vue-loader')
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const WebpackAssetsManifest = require('webpack-assets-manifest');

const isDev = process.env.NODE_ENV === 'development';

module.exports = {
	entry: path.resolve(__dirname, './assets/main.js'),
	output: {
		path: path.resolve(__dirname, './www/dist'),
		publicPath: "/dist/",
		filename: 'bundle.js',
	},
	resolve: {
		extensions: ['*', '.js', '.vue'],
	},
	module: {
		rules: [
			{
				test: /\.(js)$/,
				exclude: /node_modules/,
				use: ['babel-loader']
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			},
			{
				test: /\.(css|scss|sass)$/,
				use: [
					isDev ? 'vue-style-loader' : MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader',
					'postcss-loader'
				]
			}
		]
	},
	devServer: {
		static: path.resolve(__dirname, './dist'),
	},
	plugins: [
		new VueLoaderPlugin(),
		new MiniCssExtractPlugin({
			filename: 'main.css'
		}),
		new WebpackAssetsManifest({
			output: path.resolve(__dirname, 'www/dist/manifest.json'),
			writeToDisk: true,
			publicPath: true,
		}),
	]
};

// ****************************
// WEBPACK DEVELOPMENT CONFIG *
// ****************************
if (isDev) {
	const development = {
		devServer: {
			host: '0.0.0.0',
			port: '8080',
			static: path.join(__dirname, 'www'),
			hot: true,
			proxy: {
				'/': `http://0.0.0.0:8000`
			}
		},
	};

	module.exports = merge(module.exports, development);
}

// ***************************
// WEBPACK PRODUCTION CONFIG *
// ***************************
if (!isDev) {
	const production = {
		devtool: 'source-map',
		plugins: [
			new CssMinimizerPlugin(),
		],
	};

	module.exports = merge(module.exports, production);
}
