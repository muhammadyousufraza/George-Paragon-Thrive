const isProduction = process.env.NODE_ENV === 'production';
const wpPot = require( 'wp-pot' );
const rootDir = 'src/externals';
const svgToMiniDataURI = require( 'mini-svg-data-uri' );
const fs = require( 'fs' );
const pages = {
	admin: {
		entry: 'src/admin.js',
		chunks: [ 'admin' ]
	}
};

fs.readdirSync( rootDir ).forEach( path => {
	if ( fs.statSync( `${rootDir}/${path}` ).isDirectory() ) {
		pages[ path ] = {
			entry: `${rootDir}/${path}/main.js`,
			chunks: [ path ]
		}
	}
} );

module.exports = {
	filenameHashing: false,
	configureWebpack: {
		devtool: isProduction ? false : 'cheap-source-map',
		optimization: {
			splitChunks: false,
			minimize: isProduction,
		},
		externals: {
			"jquery": "jQuery"
		},
	},

	chainWebpack: config => {
		if ( isProduction ) {
			wpPot( {
				destFile: '../languages/thrive-automator.po',
				package: 'Thrive Automator',
				domain: 'thrive-automator',
				src: [
					'../inc/**/*.php',
				]
			} )
		}

		Object.keys( pages ).forEach( page => config.plugins.delete( `html-${page}` ) );

		const imagesRule = config.module.rule( 'images' );

		imagesRule.set( 'parser', {
			dataUrlCondition: {
				maxSize: 150 * 1024
			}
		} )

		const svgRule = config.module.rule( 'svg' );
		svgRule.type( 'asset/inline' );
		svgRule.set( 'generator', {
			dataUrl: content => {
				content = content.toString();
				return svgToMiniDataURI( content );
			}
		} )
	},
	pages: {
		...pages,
	},
};
