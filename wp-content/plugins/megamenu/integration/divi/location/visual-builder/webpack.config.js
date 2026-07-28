const path = require( 'path' );

module.exports = {
	entry: {
		bundle: './src/index.jsx',
	},
	// Divi Visual Builder uses these scripts from its own global scope.
	externals: {
		react: 'React',
	},
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				exclude: /node_modules/,
				use: [
					{
						loader: 'thread-loader',
						options: {
							workers: -1,
						},
					},
					{
						loader: 'babel-loader',
						options: {
							compact: false,
							presets: [
								[ '@babel/preset-env', {
									modules: false,
									targets: '> 5%',
								} ],
								'@babel/preset-react',
							],
							cacheDirectory: false,
						},
					},
				],
			},
		],
	},
	resolve: {
		extensions: [ '.js', '.jsx' ],
	},
	output: {
		filename: 'maxmegamenu-location-module.js',
		path: path.resolve( __dirname, 'build' ),
	},
};
