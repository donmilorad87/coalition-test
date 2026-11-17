/**
 * Theme Settings page: media pickers (logo, social icons) and the social links repeater.
 */
class MediaField {
	/**
	 * @param {HTMLElement} root Element with [data-ct-media-field].
	 */
	constructor( root ) {
		this.input = root.querySelector( '[data-ct-media-input]' );
		this.preview = root.querySelector( '[data-ct-media-preview]' );
		this.selectButton = root.querySelector( '[data-ct-media-select]' );
		this.removeButton = root.querySelector( '[data-ct-media-remove]' );
		this.frame = null;
	}

	init() {
		this.selectButton.addEventListener( 'click', () => this.open() );
		this.removeButton.addEventListener( 'click', () => this.set( 0, '' ) );
	}

	open() {
		this.frame ??= window.wp.media( {
			title: this.selectButton.dataset.title,
			library: { type: 'image' },
			multiple: false,
		} );
		this.frame.off( 'select' ).on( 'select', () => {
			const image = this.frame
				.state()
				.get( 'selection' )
				.first()
				.toJSON();
			this.set( image.id, image.sizes?.medium?.url ?? image.url );
		} );
		this.frame.open();
	}

	/**
	 * @param {number} id  Attachment ID (0 clears).
	 * @param {string} url Preview URL.
	 */
	set( id, url ) {
		this.input.value = String( id );
		this.preview.replaceChildren();
		if ( url ) {
			const img = document.createElement( 'img' );
			img.src = url;
			img.alt = '';
			this.preview.append( img );
		}
		this.removeButton.hidden = ! id;
	}
}

/**
 * Social links repeater: rows are cloned from a server-rendered <template>.
 */
class SocialsRepeater {
	/**
	 * @param {HTMLElement} root Element with [data-ct-socials].
	 */
	constructor( root ) {
		this.list = root.querySelector( '[data-ct-socials-list]' );
		this.template = root.querySelector( '[data-ct-socials-template]' );
		this.addButton = root.querySelector( '[data-ct-socials-add]' );
	}

	init() {
		this.addButton.addEventListener( 'click', () => this.add() );
		this.list.addEventListener( 'click', ( event ) => {
			event.target
				.closest( '[data-ct-socials-remove]' )
				?.closest( '[data-ct-socials-row]' )
				.remove();
		} );
	}

	add() {
		// Unique index so new rows never collide with existing input names.
		const index = `n${ Date.now() }`;
		const html = this.template.innerHTML.replaceAll( '__index__', index );
		this.list.insertAdjacentHTML( 'beforeend', html );

		const row = this.list.lastElementChild;
		row.querySelectorAll( '[data-ct-media-field]' ).forEach( ( field ) =>
			new MediaField( field ).init()
		);
		row.querySelector( 'input[type="text"]' )?.focus();
	}
}

// Fields inside <template> are not in the document, so only live fields get bound here.
document
	.querySelectorAll( '[data-ct-media-field]' )
	.forEach( ( root ) => new MediaField( root ).init() );
document
	.querySelectorAll( '[data-ct-socials]' )
	.forEach( ( root ) => new SocialsRepeater( root ).init() );
