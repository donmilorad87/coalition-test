<?php
/**
 * Gutenberg blocks, block category and block styles.
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Registers every block built into build/blocks/<name>/block.json.
 * Block patterns live in /patterns and are registered by core automatically.
 */
final class Blocks {

	public const CATEGORY = 'ct-custom';

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'init', array( self::class, 'register_blocks' ) );
		add_action( 'init', array( self::class, 'register_block_styles' ) );
		add_filter( 'block_categories_all', array( self::class, 'add_category' ) );
		// Contact Form 7: our form markup controls its own layout, no auto <p>/<br>.
		add_filter( 'wpcf7_autop_or_not', '__return_false' );
	}

	/**
	 * Register blocks from build metadata.
	 */
	public static function register_blocks(): void {
		foreach ( (array) glob( CT_CUSTOM_BUILD . 'blocks/*/block.json' ) as $metadata ) {
			if ( ! register_block_type( dirname( $metadata ) ) ) {
				error_log( "ct-custom: failed to register block {$metadata}" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Block styles from the design, plus the pattern category used by /patterns.
	 */
	public static function register_block_styles(): void {
		register_block_pattern_category( self::CATEGORY, array( 'label' => __( 'CT Custom', 'ct-custom' ) ) );

		register_block_style(
			'core/paragraph',
			array(
				'name'  => 'ct-intro',
				'label' => __( 'Intro (muted)', 'ct-custom' ),
			)
		);
		register_block_style(
			'core/heading',
			array(
				'name'  => 'ct-section',
				'label' => __( 'Section title (bar)', 'ct-custom' ),
			)
		);
	}

	/**
	 * Theme block category first in the inserter.
	 *
	 * @param array $categories Registered categories.
	 * @return array
	 */
	public static function add_category( array $categories ): array {
		array_unshift(
			$categories,
			array(
				'slug'  => self::CATEGORY,
				'title' => __( 'CT Custom', 'ct-custom' ),
			)
		);
		return $categories;
	}
}
