/**
 * Dev tool: screenshot the homepage at the PSD size (1600px) in the "menu open" state shown in the design,
 * then write a side-by-side overlay so spacing can be tuned. Not a pass/fail test.
 *
 *   node tests/e2e/design-diff.mjs <design.png> <out-dir>
 */
import { chromium } from '@playwright/test';

const [ , , design, outDir = '.' ] = process.argv;
const browser = await chromium.launch( {
	args: [
		`--host-resolver-rules=MAP coalitiontest.local ${
			process.env.CT_HOST_IP || '127.0.0.6'
		}`,
	],
} );
const page = await browser.newPage( {
	viewport: { width: 1600, height: 756 },
} );
await page.goto( 'http://coalitiontest.local/' );
await page.evaluate( () => document.fonts.ready );
// Design state: Title 2 → Submenu 3 dropdowns open, Facebook icon hovered.
await page.addStyleTag( {
	content: `*,*::before,*::after{transition:none!important}
	.current-menu-ancestor > .sub-menu{visibility:visible!important;opacity:1!important;transition:none!important}
	.main-nav__menu > .current-menu-ancestor > .sub-menu{transform:translate(-50%,0)!important}
	.sub-menu .current-menu-ancestor > .sub-menu{transform:none!important}
	.social-links__link--facebook{background:var(--ct-social-hover-bg)!important}`,
} );
await page.screenshot( { path: `${ outDir }/site.png` } );
await browser.close();
console.log( `wrote ${ outDir }/site.png (compare with ${ design })` );
