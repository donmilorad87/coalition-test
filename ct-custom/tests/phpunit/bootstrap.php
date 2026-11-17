<?php
/**
 * PHPUnit bootstrap: boots the WordPress test suite with ct-custom as the active theme.
 *
 * Run inside the php container:
 *   docker compose exec -w /var/www/html/wp-content/themes/ct-custom php vendor/bin/phpunit
 *
 * @package CtCustom
 */

$ct_theme_dir = dirname( __DIR__, 2 );

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );
require_once $ct_theme_dir . '/vendor/autoload.php';

$ct_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
require_once $ct_tests_dir . '/includes/functions.php';

tests_add_filter(
	'setup_theme',
	static function () {
		switch_theme( 'ct-custom' );
	}
);

require $ct_tests_dir . '/includes/bootstrap.php';
