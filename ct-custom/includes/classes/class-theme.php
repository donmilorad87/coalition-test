<?php
/**
 * Theme bootstrap.
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Starts every theme module. Each module registers its own hooks in start().
 */
final class Theme {

	/**
	 * Modules in boot order.
	 *
	 * @var string[]
	 */
	private const MODULES = array(
		Setup::class,
		Assets::class,
		Fonts::class,
		Settings_Page::class,
		Mailer::class,
		Customizer::class,
		Menu::class,
		Breadcrumbs::class,
		Blocks::class,
	);

	/**
	 * Start all modules.
	 */
	public static function start(): void {
		foreach ( self::MODULES as $module ) {
			$module::start();
		}
	}
}
