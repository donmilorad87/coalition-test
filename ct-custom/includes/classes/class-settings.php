<?php
/**
 * Theme Settings repository: defaults, reads and sanitizing.
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Single source of truth for the Theme Settings option.
 */
final class Settings {

	public const OPTION = 'ct_custom_settings';

	/**
	 * Upper bound for social link rows (keeps the admin form and output bounded).
	 */
	public const MAX_SOCIALS = 20;

	/**
	 * Default values (taken from the design).
	 *
	 * @return array<string, int|string|array>
	 */
	public static function defaults(): array {
		return array(
			'logo_id'         => 0,
			'company'         => 'Coalition Skills Test',
			'address'         => "535 La Plata Street\n4200 Argentina",
			'phone'           => '385.154.11.28.38',
			'fax'             => '385.154.35.66.78',
			'socials'         => array(),
			'mail_enabled'    => false,
			'mail_host'       => '',
			'mail_port'       => 587,
			'mail_encryption' => 'tls',
			'mail_username'   => '',
			'mail_password'   => '',
			'mail_from_email' => '',
			'mail_from_name'  => '',
		);
	}

	/**
	 * Read one setting; missing keys fall back to defaults.
	 *
	 * @param string $key Setting key.
	 * @return bool|int|string|array
	 */
	public static function get( string $key ): bool|int|string|array {
		$stored = get_option( self::OPTION, array() );
		$values = wp_parse_args( is_array( $stored ) ? $stored : array(), self::defaults() );

		return $values[ $key ] ?? '';
	}

	/**
	 * `get_site_icon_url` filter: with no Site Icon set (Customizer → Site Identity), use the logo.
	 *
	 * @param string $url Site icon URL from core ('' when none is set).
	 */
	public static function site_icon_fallback( string $url ): string {
		$logo_id = (int) self::get( 'logo_id' );
		if ( '' !== $url || ! $logo_id ) {
			return $url;
		}
		return (string) wp_get_attachment_image_url( $logo_id, 'full' );
	}

	/**
	 * Social link items: list of [ name, url, image_id ].
	 *
	 * @return array<int, array{name: string, url: string, image_id: int}>
	 */
	public static function socials(): array {
		$items = self::get( 'socials' );
		return is_array( $items ) ? $items : array();
	}

	/**
	 * Normalize social rows (from the settings form or block attributes). Rows without a name or an
	 * http(s) URL are dropped; image IDs must point to an image attachment. Accepts `image_id` or `imageId`.
	 *
	 * @param mixed $rows Raw rows.
	 * @return array<int, array{name: string, url: string, image_id: int}>
	 */
	public static function sanitize_social_items( mixed $rows ): array {
		$items = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			if ( ! is_array( $row ) || count( $items ) >= self::MAX_SOCIALS ) {
				continue;
			}
			$name = sanitize_text_field( (string) ( $row['name'] ?? '' ) );
			$url  = esc_url_raw( (string) ( $row['url'] ?? '' ), array( 'http', 'https' ) );
			if ( '' === $name || '' === $url ) {
				continue;
			}
			$items[] = array(
				'name'     => $name,
				'url'      => $url,
				'image_id' => self::sanitize_image_id( $row['image_id'] ?? $row['imageId'] ?? 0 ),
			);
		}
		return $items;
	}

	/**
	 * Sanitize callback for register_setting(). Unknown keys are dropped.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array<string, int|string|array>
	 */
	public static function sanitize( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			return self::defaults();
		}

		$clean = array();
		foreach ( array_keys( self::defaults() ) as $key ) {
			$value         = $input[ $key ] ?? ( 'socials' === $key ? array() : '' );
			$clean[ $key ] = match ( true ) {
				'logo_id' === $key                       => self::sanitize_image_id( $value ),
				'address' === $key                       => sanitize_textarea_field( (string) $value ),
				in_array( $key, array( 'phone', 'fax' ), true ) => self::sanitize_phone( (string) $value ),
				'socials' === $key                       => self::sanitize_social_items( $value ),
				0 === strpos( $key, 'mail_' ) => self::sanitize_mail_field( $key, $value ),
				default                                  => sanitize_text_field( (string) $value ),
			};
		}
		return $clean;
	}

	/**
	 * Email (SMTP) fields.
	 *
	 * @param string $key   Field key (mail_*).
	 * @param mixed  $value Raw value.
	 * @return bool|int|string
	 */
	private static function sanitize_mail_field( string $key, mixed $value ): bool|int|string {
		$text = is_scalar( $value ) ? (string) $value : '';

		switch ( $key ) {
			case 'mail_enabled':
				return rest_sanitize_boolean( $text );
			case 'mail_host':
				return (string) preg_replace( '/[^a-z0-9.\-]/i', '', sanitize_text_field( $text ) );
			case 'mail_port':
				$port = (int) $text;
				return ( $port >= 1 && $port <= 65535 ) ? $port : 587;
			case 'mail_encryption':
				return in_array( $text, array( '', 'tls', 'ssl' ), true ) ? $text : '';
			case 'mail_password':
				// Stored verbatim (any character is valid); a blank submit keeps the saved password.
				$password = trim( $text );
				return '' !== $password ? $password : (string) self::get( 'mail_password' );
			case 'mail_from_email':
				return sanitize_email( $text );
			default:
				return sanitize_text_field( $text );
		}
	}

	/**
	 * Keep only characters that belong in a phone/fax number.
	 *
	 * @param string $value Raw value.
	 */
	public static function sanitize_phone( string $value ): string {
		$value = preg_replace( '/[^0-9+().\s-]/', '', sanitize_text_field( $value ) );
		return trim( (string) preg_replace( '/\s+/', ' ', (string) $value ) );
	}

	/**
	 * Build a tel: href from a display number.
	 *
	 * @param string $number Display number.
	 */
	public static function tel_href( string $number ): string {
		$digits = preg_replace( '/[^0-9+]/', '', $number );
		return '' === $digits ? '' : 'tel:' . $digits;
	}

	/**
	 * Attachment ID that points to an existing image, else 0.
	 *
	 * @param mixed $value Raw ID.
	 */
	private static function sanitize_image_id( mixed $value ): int {
		$id = is_numeric( $value ) ? (int) $value : 0;
		return ( $id > 0 && wp_attachment_is_image( $id ) ) ? $id : 0;
	}
}
