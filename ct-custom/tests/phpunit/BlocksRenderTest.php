<?php
/**
 * Dynamic block rendering: per-block overrides fall back to Theme Settings.
 *
 * @package CtCustom
 */

use CtCustom\Settings;

/**
 * @coversNothing
 */
class BlocksRenderTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( Settings::OPTION );
		parent::tear_down();
	}

	public function test_blocks_are_registered(): void {
		$registry = WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'ct-custom/reach-us' ) );
		$this->assertTrue( $registry->is_registered( 'ct-custom/social-links' ) );
	}

	public function test_reach_us_falls_back_to_theme_settings(): void {
		update_option( Settings::OPTION, array( 'company' => 'ACME Ltd', 'phone' => '111', 'fax' => '222' ) );
		$html = do_blocks( '<!-- wp:ct-custom/reach-us /-->' );

		$this->assertStringContainsString( 'ACME Ltd', $html );
		$this->assertStringContainsString( 'Phone: <a href="tel:111"', $html );
		$this->assertStringContainsString( '222', $html );
		$this->assertStringContainsString( 'data-wp-interactive="ct-custom/reach-us"', $html );
	}

	public function test_reach_us_block_attribute_overrides_settings(): void {
		update_option( Settings::OPTION, array( 'company' => 'ACME Ltd' ) );
		$html = do_blocks( '<!-- wp:ct-custom/reach-us {"company":"Override Co"} /-->' );

		$this->assertStringContainsString( 'Override Co', $html );
		$this->assertStringNotContainsString( 'ACME Ltd', $html );
	}

	public function test_reach_us_escapes_output(): void {
		$html = do_blocks( '<!-- wp:ct-custom/reach-us {"company":"<script>x</script>"} /-->' );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '&lt;script&gt;x&lt;/script&gt;', $html );
	}

	public function test_social_links_render_only_filled_networks(): void {
		update_option( Settings::OPTION, array( 'socials' => array( array( 'name' => 'Facebook', 'url' => 'https://fb.com/x', 'image_id' => 0 ) ) ) );
		$html = do_blocks( '<!-- wp:ct-custom/social-links /-->' );

		$this->assertStringContainsString( 'href="https://fb.com/x"', $html );
		$this->assertStringNotContainsString( 'social-links__link--twitter', $html );
	}

	/**
	 * Render the social links block with the given attributes.
	 *
	 * @param array $attrs Block attributes.
	 */
	private function render_social( array $attrs ): string {
		return do_blocks( '<!-- wp:ct-custom/social-links ' . wp_json_encode( $attrs ) . ' /-->' );
	}

	public function test_social_links_items_replace_settings_and_keep_order(): void {
		update_option( Settings::OPTION, array( 'socials' => array( array( 'name' => 'Facebook', 'url' => 'https://fb.com/x', 'image_id' => 0 ) ) ) );
		$html = $this->render_social(
			array(
				'items' => array(
					array( 'id' => 'a', 'name' => 'Dribbble', 'url' => 'https://dribbble.com/ct' ),
					array( 'id' => 'b', 'name' => 'Twitter', 'url' => 'https://twitter.com/ct' ),
				),
			)
		);

		$this->assertStringNotContainsString( 'fb.com', $html, 'Items replace the Theme Settings links.' );
		$this->assertLessThan( strpos( $html, 'twitter.com/ct' ), strpos( $html, 'dribbble.com/ct' ) );
		$this->assertStringContainsString( '<span class="screen-reader-text">Dribbble</span>', $html );
	}

	public function test_social_links_item_icon_fallbacks(): void {
		$html = $this->render_social(
			array(
				'items' => array(
					array( 'id' => 'a', 'name' => 'LinkedIn', 'url' => 'https://linkedin.com/ct' ),
					array( 'id' => 'b', 'name' => 'Dribbble', 'url' => 'https://dribbble.com/ct' ),
				),
			)
		);

		$this->assertStringContainsString( 'ct-icon--linkedin', $html, 'Known network name uses the built-in icon.' );
		$this->assertStringContainsString( '<span class="social-links__initial" aria-hidden="true">D</span>', $html );
	}

	public function test_social_links_item_image_is_rendered(): void {
		$image_id = self::factory()->attachment->create_object( array( 'file' => 'icon.png', 'post_mime_type' => 'image/png' ) );
		$html     = $this->render_social(
			array( 'items' => array( array( 'id' => 'a', 'name' => 'Custom', 'url' => 'https://example.com', 'imageId' => $image_id ) ) )
		);

		$this->assertMatchesRegularExpression( '/<img[^>]+class="social-links__img"/', $html );
		$this->assertStringNotContainsString( 'social-links__initial', $html );
	}

	public function test_social_links_items_with_bad_url_are_skipped_and_escaped(): void {
		$html = $this->render_social(
			array(
				'items' => array(
					array( 'id' => 'a', 'name' => 'Evil', 'url' => 'javascript:alert(1)' ),
					array( 'id' => 'b', 'name' => '<b>Ok</b>', 'url' => 'https://ok.example' ),
				),
			)
		);

		$this->assertStringNotContainsString( 'javascript:', $html );
		$this->assertStringNotContainsString( 'Evil', $html );
		$this->assertStringNotContainsString( '<b>Ok</b>', $html );
		$this->assertStringContainsString( 'href="https://ok.example"', $html );
	}

	public function test_social_links_invalid_items_attribute_falls_back_to_settings(): void {
		update_option( Settings::OPTION, array( 'socials' => array( array( 'name' => 'Pinterest', 'url' => 'https://pin.it/p', 'image_id' => 0 ) ) ) );
		$this->assertStringContainsString( 'href="https://pin.it/p"', $this->render_social( array( 'items' => array( array( 'name' => 'No url' ) ) ) ) );
	}

	public function test_social_links_share_button_only_when_enabled(): void {
		$this->assertStringNotContainsString( 'social-links__share', do_blocks( '<!-- wp:ct-custom/social-links /-->' ) );
		$this->assertStringContainsString( 'social-links__share', do_blocks( '<!-- wp:ct-custom/social-links {"showShare":true} /-->' ) );
	}
}
