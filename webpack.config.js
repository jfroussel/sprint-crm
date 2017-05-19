var webpack = require('webpack');
var path = require('path');
var BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

var entry = {
	"app": [ './app.js' ],
	"app-lead": [ './app-lead.js' ],
	"app-outsourcing": [ './app-outsourcing.js' ],
	"app-marketplace": [ './app-marketplace.js' ],
};

var alias = {
	"app": "./app/app",
	"jquery": "jquery",
	"fileinput-img": "./vendor/bower-asset/fileinput-img/fileinput-img",
	"formControlEnhance": "./js/jquery.formControlEnhance",
	"app.datatables": "./app/directive/datatables",
	"validate": "./app/adapters/validate",
    "notify-js": "./vendor/bower-asset/notifyjs/dist/notify",
};

var dependencies = {		
	
};

var config = {
    context: path.resolve(__dirname, "app"),
    entry: entry,
    output: {
        path: path.resolve('dist'),
        filename: '[name].js',
        publicPath: 'dist/',
    },
	
	resolve: {
		alias: alias,
		modules: [
			'app',
			'node_modules',
		],
	},
	
	plugins: [
		new webpack.optimize.CommonsChunkPlugin({
			name: 'vendor',
			filename: "vendor.js",
			minChunks: function(module) {
				return module.resource && ( /node_modules/.test(module.resource) || /vendor/.test(module.resource) );
			}
		}),
		new webpack.optimize.CommonsChunkPlugin({
			name: 'common',
			filename: "common.js",
			minChunks: 2,
			chunks: [
				"app",
				"app-lead",
				"app-outsourcing",
				"app-marketplace",
			],
		}),
		
		/*
		new BundleAnalyzerPlugin({
		  // Can be `server`, `static` or `disabled`.
		  // In `server` mode analyzer will start HTTP server to show bundle report.
		  // In `static` mode single HTML file with bundle report will be generated.
		  // In `disabled` mode you can use this plugin to just generate Webpack Stats JSON file by setting `generateStatsFile` to `true`.
		  analyzerMode: 'server',
		  // Host that will be used in `server` mode to start HTTP server.
		  analyzerHost: '127.0.0.1',
		  // Port that will be used in `server` mode to start HTTP server.
		  analyzerPort: 8888,
		  // Path to bundle report file that will be generated in `static` mode.
		  // Relative to bundles output directory.
		  reportFilename: 'report.html',
		  // Module sizes to show in report by default.
		  // Should be one of `stat`, `parsed` or `gzip`.
		  // See "Definitions" section for more information.
		  defaultSizes: 'parsed',
		  // Automatically open report in default browser
		  openAnalyzer: false,
		  // If `true`, Webpack Stats JSON file will be generated in bundles output directory
		  generateStatsFile: false,
		  // Name of Webpack Stats JSON file that will be generated if `generateStatsFile` is `true`.
		  // Relative to bundles output directory.
		  statsFilename: 'stats.json',
		  // Options for `stats.toJson()` method.
		  // For example you can exclude sources of your modules from stats file with `source: false` option.
		  // See more options here: https://github.com/webpack/webpack/blob/webpack-1/lib/Stats.js#L21
		  statsOptions: null,
		  // Log level. Can be 'info', 'warn', 'error' or 'silent'.
		  logLevel: 'info'
		}),
		*/
		
		//new webpack.optimize.UglifyJsPlugin({
			//minimize: true
		//}),
		
		new webpack.ProvidePlugin({
			"$": 'jquery',
			"jQuery": 'jquery',
			"window.$": "jquery",
			"window.jQuery": "jquery",
		}),


    ],
	
    module: {
		rules: [
			{
				test: /\.js?$/,
                exclude: /node_modules/,
				loader: "babel-loader",
				query: {
					plugins: [
						'transform-class-properties',
						'transform-runtime'
					],
					presets: ['stage-0']
				}
			},
			{
				test: /\.css$/,
				use: [
					'style-loader',
					{
						loader: 'css-loader',
						options: { modules: true },
					},
				],
			},
			{
				test: /\.(sass|scss)$/,
				use: [
					'style-loader',
					'css-loader',
					'sass-loader',
				]
			},
			{
				test: /\.json$/,
				loader: "json-loader"
			},
			{
				test: /\.(jpe?g|png|gif)$/i,
				loader:"file"
			},
		],
    },
    externals: {
        
    },
    devtool: 'eval'
};

var resolveRelativePath = function(file, enforceAbsolute){
	if(file.substr(0,2)=='./'||file.substr(0,3)=='../'){
		file = path.resolve(file);
	}
	else if(enforceAbsolute){
		file = require.resolve(file);
	}
	return file;
};

Object.keys(alias).forEach(function(key){
	alias[key] = resolveRelativePath(alias[key],true);
});
Object.keys(dependencies).forEach(function(key){
	var deps = dependencies[key];
	var file = config.resolve.alias[key] || key;
	file = resolveRelativePath(file, true);
	var query = {};
	deps.forEach(function(dep,i){
		dep = resolveRelativePath(dep);
		var k = '__required._'+i;
		query[k] = dep;
	});
	config.module.rules.push({
		test: file,
		loader: 'imports-loader',
		query: query,
	});
});

//console.log(JSON.stringify(config, null, 2));

module.exports = config;
