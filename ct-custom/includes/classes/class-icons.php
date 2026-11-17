<?php
/**
 * Inline SVG icons (24×24, currentColor).
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Icon registry. Paths adapted from Material Design Icons (Apache 2.0) / Remix Icon (Apache 2.0).
 */
final class Icons {

	private const PATHS = array(
		'facebook'  => 'M13.5 22v-8.2h2.8l.4-3.2h-3.2V8.6c0-.9.3-1.6 1.6-1.6h1.7V4.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.1 1.5-4.1 4.2v2.4H7.4v3.2h2.8V22z',
		'twitter'   => 'M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05A4.28 4.28 0 0 0 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.2 4.2 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98A8.52 8.52 0 0 1 1.04 19c-.34 0-.68-.02-1.02-.06A12.1 12.1 0 0 0 8.12 21C16 21 20.33 14.46 20.33 8.79l-.01-.56c.84-.6 1.56-1.36 2.14-2.23z',
		'linkedin'  => 'M6.94 5a2 2 0 1 1-4-.002 2 2 0 0 1 4 .002zM7 8.48H3V21h4zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91z',
		'pinterest' => 'M12 2a10 10 0 0 0-3.64 19.31c-.09-.78-.17-1.98.04-2.83l1.15-4.94s-.29-.59-.29-1.46c0-1.37.79-2.39 1.78-2.39.84 0 1.25.63 1.25 1.39 0 .85-.54 2.11-.82 3.29-.23.98.5 1.79 1.47 1.79 1.77 0 3.13-1.86 3.13-4.55 0-2.38-1.71-4.04-4.15-4.04-2.83 0-4.49 2.12-4.49 4.31 0 .85.33 1.77.74 2.27.08.1.09.18.07.28l-.28 1.13c-.04.18-.15.22-.33.13-1.25-.58-2.03-2.4-2.03-3.87 0-3.15 2.29-6.04 6.6-6.04 3.46 0 6.16 2.47 6.16 5.77 0 3.45-2.17 6.22-5.19 6.22-1.01 0-1.97-.53-2.29-1.15l-.62 2.38c-.23.87-.84 1.96-1.25 2.62A10 10 0 1 0 12 2z',
		'share'     => 'M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11A3 3 0 1 0 15 5c0 .24.04.47.09.7L8.04 9.81a3 3 0 1 0 0 4.38l7.12 4.16c-.05.21-.08.43-.08.65A2.92 2.92 0 1 0 18 16.08z',
		'copy'      => 'M16 1H4a2 2 0 0 0-2 2v14h2V3h12zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2zm0 16H8V7h11z',
		'chevron'   => 'M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z',
		'menu'      => 'M3 6h18v2H3zm0 5h18v2H3zm0 5h18v2H3z',
		'close'     => 'M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z',
	);

	/**
	 * Whether an icon exists for the given key.
	 *
	 * @param string $name Icon key.
	 */
	public static function has( string $name ): bool {
		return isset( self::PATHS[ $name ] );
	}

	/**
	 * Decorative SVG markup (aria-hidden); pair it with visible or screen-reader text.
	 *
	 * @param string $name Icon key.
	 */
	public static function get( string $name ): string {
		if ( ! isset( self::PATHS[ $name ] ) ) {
			return '';
		}
		return '<svg class="ct-icon ct-icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . self::PATHS[ $name ] . '"/></svg>';
	}
}
