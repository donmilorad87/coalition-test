<?php
/**
 * Turns Customizer values into CSS custom properties.
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the :root {} block printed inline after the theme stylesheet.
 */
final class Customizer_Css {

	/**
	 * `:root{--ct-…:…;}` for every schema field. Stored values are re-validated, so a bad DB value can't inject CSS.
	 */
	public static function root_css(): string {
		$css = ':root{';
		foreach ( Customizer_Schema::fields() as $id => $field ) {
			$value = self::sanitize_value( get_theme_mod( $id, $field['default'] ), $field );
			if ( 'font' === $field['type'] ) {
				$value = Fonts::families()[ $value ]['stack'] ?? $field['fallback'];
			}
			$css .= $field['var'] . ':' . $value . ( $field['unit'] ?? '' ) . ';';
		}
		return $css . '}';
	}

	/**
	 * Validate one value against its field; invalid input returns the field default.
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Schema field.
	 * @return int|float|string
	 */
	public static function sanitize_value( mixed $value, array $field ): int|float|string {
		if ( 'font' === $field['type'] ) {
			$slug = sanitize_key( is_string( $value ) ? $value : '' );
			return isset( Fonts::families()[ $slug ] ) ? $slug : $field['default'];
		}

		if ( 'color' === $field['type'] ) {
			$color = sanitize_hex_color( is_string( $value ) ? $value : '' );
			return $color ? $color : $field['default'];
		}

		if ( ! is_numeric( $value ) ) {
			return $field['default'];
		}
		$number = $value + 0;

		return ( $number < $field['min'] || $number > $field['max'] ) ? $field['default'] : $number;
	}
}
