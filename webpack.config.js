const path = require('path')
// SPDX-FileCopyrightText: Aao Business Chat <info@aaochat.com>
// SPDX-License-Identifier: AGPL-3.0-or-later
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	'aaochat_sidebar': path.join(__dirname, 'src', 'aaochat_sidebar.js'),
	'admin': path.join(__dirname, 'src', 'admin.js'),
	'adminsetting': path.join(__dirname, 'src', 'adminsetting.js'),
	'authkey': path.join(__dirname, 'src', 'authkey.js'),
	'jquery_cookie.min': path.join(__dirname, 'src', 'jquery_cookie.min.js'),
	'jquery_fancybox': path.join(__dirname, 'src', 'jquery_fancybox.js'),
	'jquery_magnific-popup': path.join(__dirname, 'src', 'jquery_magnific-popup.js'),
	'script': path.join(__dirname, 'src', 'script.js'),
	//dashboard: path.join(__dirname, 'src', 'dashboard.js'),
	//personalSettings: path.join(__dirname, 'src', 'settings-personal.js'),
	//adminSettings: path.join(__dirname, 'src', 'settings-admin.js'),
}

webpackConfig.output = {
	path: path.resolve(__dirname, './js/'),
	publicPath: '/js/',
	filename: '[name].js',
	chunkFilename: 'aaochat.[id].js?v=[chunkhash]'
	//jsonpFunction: 'webpackJsonpAaochat'
}	

webpackConfig.optimization = {
	splitChunks: {
		cacheGroups: {
			defaultVendors: false,
		},
	},
}

webpackConfig.module.rules.push({
	resourceQuery: /raw/,
	type: 'asset/source',
})

/*
webpackConfig.plugins.push(
	new CopyPlugin({
		patterns: [
			{ from: 'src/legacy_scripts', to: '' },
		],
	})
)*/

module.exports = webpackConfig
/*module.exports = {
    mode: 'development',
  // mode: 'production',
	...webpackConfig,
	entry: {
		'aaochat_sidebar': path.resolve(path.join('src', 'aaochat_sidebar.js')),
	},
}*/

/*
module.exports = {
	entry: {
		'aaochat_sidebar': path.join(__dirname, 'src', 'aaochat_sidebar.js'),
	},
	output: {
		path: path.resolve(__dirname, './js/'),
		publicPath: '/js/',
		filename: '[name].js',
		chunkFilename: 'aaochat_sidebar.[id].js?v=[chunkhash]',
		jsonpFunction: 'webpackJsonpFilesAaochat'
	}
}*/
