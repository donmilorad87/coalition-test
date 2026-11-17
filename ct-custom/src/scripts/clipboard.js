/**
 * Copy text to the clipboard. Uses the async Clipboard API on secure origins and falls back to
 * execCommand on plain-http sites (e.g. http://coalitiontest.local), where navigator.clipboard is undefined.
 *
 * @param {string} text Text to copy.
 * @return {Promise<void>} Rejects when the copy failed.
 */
export async function copyText( text ) {
	if ( window.isSecureContext && navigator.clipboard ) {
		return navigator.clipboard.writeText( text );
	}

	const field = document.createElement( 'textarea' );
	field.value = text;
	field.setAttribute( 'readonly', '' );
	field.style.cssText = 'position:fixed;inset:0 auto auto 0;opacity:0';
	document.body.append( field );
	field.select();
	const copied = document.execCommand( 'copy' );
	field.remove();

	if ( ! copied ) {
		throw new Error( 'execCommand("copy") returned false' );
	}
}
