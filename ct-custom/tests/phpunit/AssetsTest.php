<?php
/**
 * Asset loading tests.
 *
 * @package CtCustom
 */

use CtCustom\Assets;
use CtCustom\Settings;

/**
 * @covers \CtCustom\Assets
 */
class AssetsTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( Settings::OPTION );
		wp_dequeue_script( 'ct-custom-editor' );
		wp_deregister_script( 'ct-custom-editor' );
		parent::tear_down();
	}

	public function test_editor_defaults_never_expose_smtp_settings(): void {
		update_option(
			Settings::OPTION,
			array(
				'company'       => 'Acme',
				'mail_enabled'  => true,
				'mail_host'     => 'smtp.example.com',
				'mail_username' => 'smtp-user',
				'mail_password' => 's3cret-pass',
			)
		);

		Assets::enqueue_editor();
		$inline = implode( '', (array) wp_scripts()->get_data( 'ct-custom-editor', 'before' ) );

		$this->assertStringContainsString( '"company":"Acme"', $inline );
		$this->assertStringNotContainsString( 'mail_', $inline );
		$this->assertStringNotContainsString( 's3cret-pass', $inline );
	}
}
