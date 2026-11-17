/**
 * Extends the wp-scripts webpack config: blocks are auto-discovered from src/blocks/<name>/block.json,
 * theme-wide entries are added to the classic script config.
 */
const path = require( 'path' );
const [
	scriptConfig,
	moduleConfig,
] = require( '@wordpress/scripts/config/webpack.config' );

const entry = ( file ) => path.resolve( __dirname, file );

module.exports = [
	{
		...scriptConfig,
		entry: {
			...scriptConfig.entry(),
			front: entry( 'front.js' ),
			editor: entry( 'editor/index.js' ),
			'admin-settings': entry( 'admin/settings.js' ),
			'customizer-preview': entry( 'customizer/preview.js' ),
		},
	},
	moduleConfig,
];
