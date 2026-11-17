<?php
/**
 * Theme Settings repository tests.
 *
 * @package CtCustom
 */

use CtCustom\Settings;

/**
 * @covers \CtCustom\Settings
 */
class SettingsTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( Settings::OPTION );
		parent::tear_down();
	}

	public function test_get_returns_default_when_option_missing(): void {
		$this->assertSame( '385.154.11.28.38', Settings::get( 'phone' ) );
		$this->assertSame( 0, Settings::get( 'logo_id' ) );
	}

	public function test_get_returns_stored_value(): void {
		update_option( Settings::OPTION, array( 'phone' => '555 123' ) );
		$this->assertSame( '555 123', Settings::get( 'phone' ) );
	}

	public function test_get_unknown_key_returns_empty_string(): void {
		$this->assertSame( '', Settings::get( 'nope' ) );
	}

	public function test_sanitize_strips_invalid_phone_characters(): void {
		$clean = Settings::sanitize( array( 'phone' => '+1 (385) 154<script>-11' ) );
		$this->assertSame( '+1 (385) 154-11', $clean['phone'] );
	}

	public function test_sanitize_keeps_multiline_address_but_strips_tags(): void {
		$clean = Settings::sanitize( array( 'address' => "535 La Plata <b>Street</b>\n4200 Argentina" ) );
		$this->assertSame( "535 La Plata Street\n4200 Argentina", $clean['address'] );
	}

	public function test_sanitize_social_items_drops_invalid_rows(): void {
		$clean = Settings::sanitize(
			array(
				'socials' => array(
					array( 'name' => 'Evil', 'url' => 'javascript:alert(1)' ),
					array( 'name' => '', 'url' => 'https://no-name.example' ),
					array( 'name' => '<b>Twitter</b>', 'url' => 'https://twitter.com/coalition', 'image_id' => '999999' ),
					'not-a-row',
				),
			)
		);
		$this->assertSame(
			array( array( 'name' => 'Twitter', 'url' => 'https://twitter.com/coalition', 'image_id' => 0 ) ),
			$clean['socials']
		);
	}

	public function test_sanitize_social_items_are_capped(): void {
		$rows  = array_fill( 0, Settings::MAX_SOCIALS + 5, array( 'name' => 'X', 'url' => 'https://x.example' ) );
		$clean = Settings::sanitize( array( 'socials' => $rows ) );
		$this->assertCount( Settings::MAX_SOCIALS, $clean['socials'] );
	}

	public function test_social_items_accept_block_attribute_keys(): void {
		$image_id = self::factory()->attachment->create_object( array( 'file' => 'icon.png', 'post_mime_type' => 'image/png' ) );
		$items    = Settings::sanitize_social_items( array( array( 'name' => 'A', 'url' => 'https://a.example', 'imageId' => $image_id ) ) );
		$this->assertSame( $image_id, $items[0]['image_id'] );
	}

	public function test_sanitize_logo_id_must_be_an_image_attachment(): void {
		$image_id = self::factory()->attachment->create_object( array( 'file' => 'logo.png', 'post_mime_type' => 'image/png' ) );
		$this->assertSame( $image_id, Settings::sanitize( array( 'logo_id' => (string) $image_id ) )['logo_id'] );
		$this->assertSame( 0, Settings::sanitize( array( 'logo_id' => '-5' ) )['logo_id'] );
		$this->assertSame( 0, Settings::sanitize( array( 'logo_id' => '999999' ) )['logo_id'] );
	}

	public function test_sanitize_drops_unknown_keys(): void {
		$this->assertArrayNotHasKey( 'evil', Settings::sanitize( array( 'evil' => 'x' ) ) );
	}

	public function test_sanitize_non_array_returns_defaults(): void {
		$this->assertSame( Settings::defaults(), Settings::sanitize( 'garbage' ) );
	}

	public function test_tel_href_keeps_digits_and_plus(): void {
		$this->assertSame( 'tel:+38515411', Settings::tel_href( '+385 (154) 11' ) );
		$this->assertSame( '', Settings::tel_href( '' ) );
	}

	public function test_socials_returns_stored_items(): void {
		$items = array( array( 'name' => 'Facebook', 'url' => 'https://fb.com/x', 'image_id' => 0 ) );
		update_option( Settings::OPTION, array( 'socials' => $items ) );
		$this->assertSame( $items, Settings::socials() );
	}

	public function test_logo_is_the_default_site_icon(): void {
		update_option( 'site_icon', 0 );
		$this->assertFalse( has_site_icon(), 'No logo, no icon.' );

		$logo_id = self::factory()->attachment->create_object( array( 'file' => 'logo.png', 'post_mime_type' => 'image/png' ) );
		update_option( Settings::OPTION, array( 'logo_id' => $logo_id ) );

		$this->assertTrue( has_site_icon() );
		$this->assertSame( wp_get_attachment_image_url( $logo_id, 'full' ), get_site_icon_url( 32 ) );
	}

	public function test_core_site_icon_wins_over_logo(): void {
		$logo_id = self::factory()->attachment->create_object( array( 'file' => 'logo.png', 'post_mime_type' => 'image/png' ) );
		$icon_id = self::factory()->attachment->create_object( array( 'file' => 'icon.png', 'post_mime_type' => 'image/png' ) );
		update_option( Settings::OPTION, array( 'logo_id' => $logo_id ) );
		update_option( 'site_icon', $icon_id );

		$this->assertStringContainsString( 'icon.png', get_site_icon_url( 32 ) );
	}
}
