/**
 * Reach Us: copy phone/fax to the clipboard with a polite status message.
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

import { copyText } from '../../scripts/clipboard';

const MESSAGE_TIMEOUT_MS = 2500;

const feedbackText = ( ref, key ) =>
	ref.closest( '.reach-us' )?.querySelector( '.reach-us__feedback' )?.dataset[
		key
	] ?? '';

store( 'ct-custom/reach-us', {
	actions: {
		*copy() {
			const context = getContext();
			const { ref } = getElement();

			try {
				yield copyText( ref.dataset.value );
				context.message = feedbackText( ref, 'copied' );
			} catch ( error ) {
				// eslint-disable-next-line no-console
				console.error( 'ct-custom/reach-us: copy failed', error );
				context.message = feedbackText( ref, 'failed' );
			}

			yield new Promise( ( resolve ) =>
				setTimeout( resolve, MESSAGE_TIMEOUT_MS )
			);
			context.message = '';
		},
	},
	callbacks: {
		// Server renders the buttons hidden so no-JS visitors never see a dead control.
		reveal() {
			getElement().ref.hidden = false;
		},
	},
} );
