/**
 * Primary navigation.
 * Desktop: dropdowns open on hover / :focus-within (CSS); Escape closes them.
 * Mobile: hamburger panel + accordion submenus driven by aria-expanded.
 */
export default class Navigation {
	/**
	 * @param {HTMLElement} root The <nav data-main-nav> element.
	 */
	constructor( root ) {
		this.root = root;
		this.toggle = root.querySelector( '.main-nav__burger' );
		this.mobileQuery = window.matchMedia( '(max-width: 991.98px)' );
	}

	init() {
		this.toggle?.addEventListener( 'click', () =>
			this.setOpen( ! this.isOpen() )
		);
		this.root.addEventListener( 'click', ( event ) =>
			this.onSubmenuToggle( event )
		);
		this.root.addEventListener( 'keydown', ( event ) =>
			this.onKeydown( event )
		);
		this.root.addEventListener( 'focusin', ( event ) =>
			this.clearDismissed( event.target )
		);
		document.addEventListener( 'click', ( event ) =>
			this.onOutsideClick( event )
		);
		this.mobileQuery.addEventListener( 'change', () => this.reset() );
	}

	isOpen() {
		return this.toggle?.getAttribute( 'aria-expanded' ) === 'true';
	}

	/**
	 * @param {boolean} open Whether the mobile panel is open.
	 */
	setOpen( open ) {
		this.toggle?.setAttribute( 'aria-expanded', String( open ) );
		this.root.classList.toggle( 'is-open', open );
	}

	/**
	 * @param {MouseEvent} event Click inside the nav.
	 */
	onSubmenuToggle( event ) {
		const button = event.target.closest( '.main-nav__toggle' );
		if ( ! button ) {
			return;
		}
		const expanded = button.getAttribute( 'aria-expanded' ) === 'true';
		button.setAttribute( 'aria-expanded', String( ! expanded ) );
		button.parentElement.classList.toggle( 'is-expanded', ! expanded );
	}

	/**
	 * @param {KeyboardEvent} event Key press inside the nav.
	 */
	onKeydown( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}
		if ( this.mobileQuery.matches && this.isOpen() ) {
			this.setOpen( false );
			this.toggle.focus();
			return;
		}
		// Desktop dropdowns are :focus-within driven; moving focus to the top-level link closes them.
		const topItem = event.target.closest( '.main-nav__menu > li' );
		topItem?.querySelector( 'a' )?.focus();
		topItem?.classList.add( 'is-dismissed' );
		topItem?.addEventListener(
			'mouseleave',
			() => topItem.classList.remove( 'is-dismissed' ),
			{ once: true }
		);
	}

	/**
	 * Re-enable dropdowns that were closed with Escape once focus moves elsewhere.
	 *
	 * @param {Element} target Newly focused element.
	 */
	clearDismissed( target ) {
		this.root.querySelectorAll( '.is-dismissed' ).forEach( ( item ) => {
			// Keep it closed only while focus sits on the item's own top-level link.
			if ( target !== item.querySelector( 'a' ) ) {
				item.classList.remove( 'is-dismissed' );
			}
		} );
	}

	/**
	 * @param {MouseEvent} event Any document click.
	 */
	onOutsideClick( event ) {
		if ( this.isOpen() && ! this.root.contains( event.target ) ) {
			this.setOpen( false );
		}
	}

	/** Collapse everything when crossing the mobile/desktop breakpoint. */
	reset() {
		this.setOpen( false );
		this.root
			.querySelectorAll( '.main-nav__toggle[aria-expanded="true"]' )
			.forEach( ( button ) => {
				button.setAttribute( 'aria-expanded', 'false' );
				button.parentElement.classList.remove( 'is-expanded' );
			} );
	}
}
