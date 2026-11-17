<?php
/**
 * Fonts from the WordPress Font Library (Appearance → Fonts).
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Reads installed + activated font families from global settings. Core prints their @font-face rules
 * (wp_print_font_faces); the theme only picks which family each role uses via the Customizer.
 */
final class Fonts {

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'wp_head', array( self::class, 'print_preloads' ), 1 );
	}

	/**
	 * Available families: slug => [ name, stack, faces ].
	 *
	 * @return array<string, array{name: string, stack: string, faces: array}>
	 */
	public static function families(): array {
		$by_origin = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );
		$families  = array();

		foreach ( is_array( $by_origin ) ? $by_origin : array() as $origin_families ) {
			foreach ( (array) $origin_families as $family ) {
				if ( empty( $family['slug'] ) || empty( $family['fontFamily'] ) ) {
					continue;
				}
				$families[ sanitize_key( $family['slug'] ) ] = array(
					'name'  => sanitize_text_field( $family['name'] ?? $family['slug'] ),
					'stack' => self::sanitize_stack( (string) $family['fontFamily'] ),
					'faces' => (array) ( $family['fontFace'] ?? array() ),
				);
			}
		}
		return $families;
	}

	/**
	 * Keep a font-family value to characters that can't break out of a CSS declaration.
	 *
	 * @param string $stack Raw font-family value.
	 */
	public static function sanitize_stack( string $stack ): string {
		return trim( (string) preg_replace( '/[^\w\s,"\'-]/', '', $stack ) );
	}

	/**
	 * Regular (400, normal) face URLs of the families selected for body and display text.
	 *
	 * @return string[]
	 */
	public static function preload_urls(): array {
		$families = self::families();
		$urls     = array();

		foreach ( array( 'ct_font_body', 'ct_font_display' ) as $id ) {
			$field = Customizer_Schema::fields()[ $id ];
			$slug  = Customizer_Css::sanitize_value( get_theme_mod( $id, $field['default'] ), $field );
			foreach ( $families[ $slug ]['faces'] ?? array() as $face ) {
				$src = is_array( $face['src'] ?? null ) ? reset( $face['src'] ) : ( $face['src'] ?? '' );
				if ( '400' === (string) ( $face['fontWeight'] ?? '' ) && 'normal' === ( $face['fontStyle'] ?? 'normal' ) && str_ends_with( (string) $src, '.woff2' ) ) {
					$urls[] = (string) $src;
					break;
				}
			}
		}
		return array_values( array_unique( $urls ) );
	}

	/**
	 * Preload the above-the-fold fonts.
	 */
	public static function print_preloads(): void {
		foreach ( self::preload_urls() as $url ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $url ) );
		}
	}
}
