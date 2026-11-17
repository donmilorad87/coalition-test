<?php
/**
 * Primary navigation enhancements.
 *
 * @package CtCustom
 */

namespace CtCustom;

use WP_Post;
use stdClass;

defined( 'ABSPATH' ) || exit;

/**
 * Adds accessible submenu toggle buttons to the primary menu (used by the mobile accordion).
 */
final class Menu {

	public const DEPTH = 3;

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_filter( 'walker_nav_menu_start_el', array( self::class, 'add_submenu_toggle' ), 10, 4 );
	}

	/**
	 * Render the primary menu.
	 */
	public static function render(): void {
		wp_nav_menu(
			array(
				'theme_location' => Setup::MENU_PRIMARY,
				'menu_id'        => 'primary-menu',
				'menu_class'     => 'main-nav__menu',
				'container'      => false,
				'depth'          => self::DEPTH,
				'fallback_cb'    => false,
			)
		);
	}

	/**
	 * Append a toggle button after links that own a submenu.
	 *
	 * @param string   $item_output Item HTML.
	 * @param WP_Post  $item        Menu item.
	 * @param int      $depth       Depth.
	 * @param stdClass $args        wp_nav_menu() args.
	 */
	public static function add_submenu_toggle( string $item_output, $item, int $depth, $args ): string {
		$is_primary   = isset( $args->theme_location ) && Setup::MENU_PRIMARY === $args->theme_location;
		$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );

		if ( ! $is_primary || ! $has_children || $depth >= self::DEPTH - 1 ) {
			return $item_output;
		}

		return $item_output . sprintf(
			'<button type="button" class="main-nav__toggle" aria-expanded="false"><span class="screen-reader-text">%s</span>%s</button>',
			/* translators: %s: parent menu item title. */
			esc_html( sprintf( __( 'Show submenu for %s', 'ct-custom' ), wp_strip_all_tags( $item->title ) ) ),
			Icons::get( 'chevron' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		);
	}
}
