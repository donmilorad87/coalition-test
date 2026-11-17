/**
 * Extends the @wordpress/scripts flat config.
 * `@wordpress/*` imports are WordPress-provided externals (wp.* globals / script modules), not npm deps.
 */
const wpConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...wpConfig,
	{ ignores: [ 'tests/e2e/.results/**' ] },
	{
		settings: {
			'import/core-modules': [
				'@wordpress/block-editor',
				'@wordpress/blocks',
				'@wordpress/components',
				'@wordpress/core-data',
				'@wordpress/data',
				'@wordpress/editor',
				'@wordpress/i18n',
				'@wordpress/interactivity',
				'@wordpress/plugins',
				'@wordpress/server-side-render',
			],
		},
	},
	{
		// Node-side dev tooling.
		files: [ 'tests/e2e/**', 'playwright.config.js', 'src/webpack.config.js' ],
		rules: { 'no-console': 'off', 'import/no-extraneous-dependencies': 'off' },
	},
];
