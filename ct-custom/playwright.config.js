// E2E + visual checks against the Docker site. coalitiontest.local is mapped to the stack's
// loopback IP inside the browser, so the tests run even without an /etc/hosts entry.
const { defineConfig, devices } = require( '@playwright/test' );

const HOST_IP = process.env.CT_HOST_IP || '127.0.0.6';

module.exports = defineConfig( {
	testDir: './tests/e2e',
	outputDir: './tests/e2e/.results',
	fullyParallel: true,
	reporter: 'list',
	use: {
		baseURL: 'http://coalitiontest.local',
		launchOptions: {
			args: [
				`--host-resolver-rules=MAP coalitiontest.local ${ HOST_IP }`,
			],
		},
	},
	projects: [
		{
			name: 'desktop',
			use: {
				...devices[ 'Desktop Chrome' ],
				viewport: { width: 1600, height: 756 },
			},
		},
		{
			name: 'mobile',
			use: { ...devices[ 'Pixel 7' ] },
		},
	],
} );
