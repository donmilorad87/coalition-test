// @ts-check
const { test, expect } = require( '@playwright/test' );

const isMobile = ( testInfo ) => testInfo.project.name === 'mobile';

test.beforeEach( async ( { page } ) => {
	await page.goto( '/' );
} );

test.describe( 'common', () => {
	test( 'top bar, breadcrumbs and title come from WordPress', async ( {
		page,
	} ) => {
		await expect( page.locator( '.top-bar__phone' ) ).toHaveText(
			'385.154.11.28.38'
		);
		await expect( page.locator( '.top-bar__phone' ) ).toHaveAttribute(
			'href',
			'tel:385154112838'
		);
		await expect(
			page.getByRole( 'link', { name: 'Login' } )
		).toBeVisible();
		await expect(
			page.getByRole( 'link', { name: 'Signup' } )
		).toBeVisible();

		const crumbs = page.getByRole( 'navigation', { name: 'Breadcrumb' } );
		await expect( crumbs.getByRole( 'listitem' ) ).toHaveText( [
			'Home',
			'Who we are',
			'Contact',
		] );
		await expect( crumbs.locator( '[aria-current="page"]' ) ).toHaveText(
			'Contact'
		);
		await expect( page.getByRole( 'heading', { level: 1 } ) ).toHaveText(
			'Contact'
		);
	} );

	test( 'Reach Us block renders settings and copies the phone number', async ( {
		page,
		context,
	} ) => {
		const reach = page.locator( '.reach-us' );
		await expect( reach ).toContainText( 'Coalition Skills Test' );
		await expect( reach ).toContainText( 'Fax: 385.154.35.66.78' );

		const copy = reach.getByRole( 'button', { name: 'Copy Phone' } );
		await reach.locator( '.reach-us__line' ).first().hover();
		await expect( copy ).toBeVisible();
		await context
			.grantPermissions( [ 'clipboard-read', 'clipboard-write' ] )
			.catch( () => {} );
		await copy.click();
		await expect( reach.getByRole( 'status' ) ).toHaveText(
			'Copied to clipboard'
		);
		await expect( reach.getByRole( 'status' ) ).toHaveText( '', {
			timeout: 5000,
		} );
	} );

	test( 'Social Links block shows the four networks, no share button by default', async ( {
		page,
	} ) => {
		const social = page.locator( '.entry-content .social-links' );
		for ( const name of [
			'Facebook',
			'Twitter',
			'LinkedIn',
			'Pinterest',
		] ) {
			await expect(
				social.getByRole( 'link', { name } )
			).toHaveAttribute( 'target', '_blank' );
		}
		await expect( social.locator( '.social-links__share' ) ).toHaveCount(
			0
		);
	} );

	test( 'contact form validates required fields', async ( { page } ) => {
		await expect( page.getByPlaceholder( 'Name *' ) ).toBeVisible();
		await expect( page.getByLabel( 'Email' ) ).toBeVisible();
		await page.getByRole( 'button', { name: 'Submit' } ).click();
		await expect(
			page.locator( '.wpcf7-not-valid-tip' ).first()
		).toBeVisible();
		await expect( page.locator( '.wpcf7-not-valid' ) ).toHaveCount( 4 );
	} );

	test( 'short pages fill the viewport, footer at the bottom', async ( {
		page,
	} ) => {
		await page.goto( '/who-we-are/' );
		const { scroll, viewport, footerBottom } = await page.evaluate(
			() => ( {
				scroll: document.documentElement.scrollHeight,
				viewport: window.innerHeight,
				footerBottom: document
					.querySelector( '.site-footer' )
					.getBoundingClientRect().bottom,
			} )
		);
		expect( scroll ).toBe( viewport );
		expect( Math.round( footerBottom ) ).toBe( viewport );
	} );

	test( 'contact form sends through the configured SMTP server', async ( {
		page,
	}, testInfo ) => {
		test.skip( isMobile( testInfo ), 'one real send per run is enough' );
		await page.getByPlaceholder( 'Name *' ).fill( 'Playwright' );
		await page.getByPlaceholder( 'Phone *' ).fill( '+385 1 234 567' );
		await page
			.getByPlaceholder( 'Email *' )
			.fill( 'playwright@example.com' );
		await page
			.getByPlaceholder( 'Message *' )
			.fill( 'E2E contact form test.' );
		await page.getByRole( 'button', { name: 'Submit' } ).click();
		await expect( page.locator( '.wpcf7-form' ) ).toHaveAttribute(
			'data-status',
			'sent',
			{ timeout: 20000 }
		);
	} );

	test( 'no horizontal scroll', async ( { page } ) => {
		const overflow = await page.evaluate(
			() =>
				document.documentElement.scrollWidth -
				document.documentElement.clientWidth
		);
		expect( overflow ).toBeLessThanOrEqual( 0 );
	} );
} );

test.describe( 'desktop navigation', () => {
	test.beforeEach( ( {}, testInfo ) =>
		test.skip( isMobile( testInfo ), 'desktop only' )
	);

	test( 'hover opens the 3-level menu', async ( { page } ) => {
		const title2 = page.locator( '.main-nav__menu > li' ).nth( 1 );
		const level2 = title2.locator( ':scope > .sub-menu' );
		const level3 = level2.locator( '.sub-menu' );

		await expect( page.locator( '.main-nav__burger' ) ).toBeHidden();
		await expect( level2 ).toBeHidden();
		await title2.locator( ':scope > a' ).hover();
		await expect( level2 ).toBeVisible();
		await level2.getByRole( 'link', { name: 'Submenu 3' } ).hover();
		await expect( level3 ).toBeVisible();
		await expect(
			level3.getByRole( 'link', { name: 'Submenu 3' } )
		).toHaveAttribute( 'aria-current', 'page' );
	} );

	test( 'keyboard focus opens dropdowns and Escape closes them', async ( {
		page,
	} ) => {
		const title2 = page.locator( '.main-nav__menu > li' ).nth( 1 );
		const level2 = title2.locator( ':scope > .sub-menu' );

		await title2.locator( ':scope > a' ).focus();
		await expect( level2 ).toBeVisible();
		await page.keyboard.press( 'Tab' );
		await expect(
			level2.getByRole( 'link', { name: 'Submenu 1' } )
		).toBeFocused();
		await page.keyboard.press( 'Escape' );
		await expect( title2.locator( ':scope > a' ) ).toBeFocused();
		await expect( level2 ).toBeHidden();
	} );
} );

test.describe( 'mobile navigation', () => {
	test.beforeEach( ( {}, testInfo ) =>
		test.skip( ! isMobile( testInfo ), 'mobile only' )
	);

	test( 'hamburger opens the panel and accordions expand submenus', async ( {
		page,
	} ) => {
		const burger = page.getByRole( 'button', {
			name: 'Menu',
			exact: true,
		} );
		const menu = page.locator( '#primary-menu' );

		await expect( menu ).toBeHidden();
		await burger.click();
		await expect( burger ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( menu ).toBeVisible();

		const toggle = page.getByRole( 'button', {
			name: 'Show submenu for Title 2',
		} );
		const level2 = page
			.locator( '.main-nav__menu > li' )
			.nth( 1 )
			.locator( ':scope > .sub-menu' );
		await expect( level2 ).toBeHidden();
		await toggle.click();
		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( level2 ).toBeVisible();

		await page
			.getByRole( 'button', { name: 'Show submenu for Submenu 3' } )
			.click();
		await expect( level2.locator( '.sub-menu' ) ).toBeVisible();

		await page.keyboard.press( 'Escape' );
		await expect( menu ).toBeHidden();
		await expect( burger ).toBeFocused();

		// Clicking outside the navigation closes the panel too.
		await burger.click();
		await expect( menu ).toBeVisible();
		await page.locator( '.top-bar' ).click( { position: { x: 5, y: 5 } } );
		await expect( menu ).toBeHidden();
	} );
} );
