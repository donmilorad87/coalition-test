/**
 * Social Links: "share this page" via the Web Share API, falling back to copying the URL.
 * Generator actions keep the directive scope, so getContext()/getElement() work after each yield.
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

import { copyText } from '../../scripts/clipboard';

const MESSAGE_TIMEOUT_MS = 2500;

const announce = ( key ) => {
	getContext().message = key ? getElement().ref.dataset[ key ] : '';
};

store( 'ct-custom/social-links', {
	actions: {
		*share() {
			const data = { title: document.title, url: window.location.href };

			try {
				if ( navigator.share ) {
					yield navigator.share( data );
					return;
				}
				yield copyText( data.url );
				announce( 'copied' );
			} catch ( error ) {
				if ( error?.name === 'AbortError' ) {
					return; // User closed the native share sheet.
				}
				// eslint-disable-next-line no-console
				console.error( 'ct-custom/social-links: share failed', error );
				announce( 'failed' );
			}

			yield new Promise( ( resolve ) =>
				setTimeout( resolve, MESSAGE_TIMEOUT_MS )
			);
			announce( '' );
		},
	},
	callbacks: {
		// Server renders the button hidden so no-JS visitors never see a dead control.
		reveal() {
			getElement().ref.hidden = false;
		},
	},
} );
