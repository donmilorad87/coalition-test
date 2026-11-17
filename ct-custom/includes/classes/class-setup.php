<?php
/**
 * Theme supports and menus.
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Core theme setup.
 */
final class Setup {

	public const MENU_PRIMARY = 'primary';

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'after_setup_theme', array( self::class, 'theme_supports' ) );
		add_action( 'after_setup_theme', array( self::class, 'content_width' ), 0 );
		add_filter( 'get_site_icon_url', array( Settings::class, 'site_icon_fallback' ) );
	}

	/**
	 * Declare theme supports and nav menu locations.
	 */
	public static function theme_supports(): void {
		load_theme_textdomain( CT_CUSTOM_TEXT_DOM, CT_CUSTOM_PATH . 'languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);

		add_editor_style( 'build/front.css' );

		register_nav_menus(
			array(
				self::MENU_PRIMARY => esc_html__( 'Primary', 'ct-custom' ),
			)
		);
	}

	/**
	 * Content width matches the 960px design container.
	 */
	public static function content_width(): void {
		$GLOBALS['content_width'] = 960; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
