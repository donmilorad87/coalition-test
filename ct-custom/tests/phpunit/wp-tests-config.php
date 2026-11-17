<?php
/**
 * WordPress test-suite config. Uses a separate database that the suite wipes on every run.
 *
 * @package CtCustom
 */

define( 'ABSPATH', '/var/www/html/' );
define( 'WP_DEFAULT_THEME', 'ct-custom' );
define( 'WP_DEBUG', true );

// Credentials come from the php container environment (docker-compose.yml ← .env).
define( 'DB_NAME', (string) getenv( 'DB_TESTS_NAME' ) );
define( 'DB_USER', (string) getenv( 'WORDPRESS_DB_USER' ) );
define( 'DB_PASSWORD', (string) getenv( 'WORDPRESS_DB_PASSWORD' ) );
define( 'DB_HOST', 'mysql' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

define( 'WP_TESTS_DOMAIN', 'coalitiontest.local' );
define( 'WP_TESTS_EMAIL', 'admin@coalitiontest.local' );
define( 'WP_TESTS_TITLE', 'CT Custom Tests' );
define( 'WP_PHP_BINARY', 'php' );
