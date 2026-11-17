<?php
/**
 * Font Library integration: families come from WordPress global settings (Appearance → Fonts).
 *
 * @package CtCustom
 */

use CtCustom\Customizer_Css;
use CtCustom\Customizer_Schema;
use CtCustom\Fonts;

/**
 * @covers \CtCustom\Fonts
 */
class FontsTest extends WP_UnitTestCase {

	/**
	 * Inject families the way an activated Font Library family appears in global settings.
	 *
	 * @param WP_Theme_JSON_Data $data Theme JSON data.
	 */
	public static function add_library_fonts( $data ) {
		return $data->update_with(
			array(
				'version'  => 3,
				'settings' => array(
					'typography' => array(
						'fontFamilies' => array(
							array(
								'name'       => 'Ubuntu',
								'slug'       => 'ubuntu',
								'fontFamily' => 'Ubuntu, sans-serif',
								'fontFace'   => array(
									array(
										'fontFamily' => 'Ubuntu',
										'fontWeight' => '400',
										'fontStyle'  => 'normal',
										'src'        => 'http://example.org/fonts/ubuntu-400.woff2',
									),
								),
							),
							array(
								'name'       => 'Evil',
								'slug'       => 'evil',
								'fontFamily' => 'Evil;}body{display:none',
							),
						),
					),
				),
			)
		);
	}

	public function set_up(): void {
		parent::set_up();
		add_filter( 'wp_theme_json_data_user', array( self::class, 'add_library_fonts' ) );
		wp_clean_theme_json_cache();
	}

	public function tear_down(): void {
		remove_filter( 'wp_theme_json_data_user', array( self::class, 'add_library_fonts' ) );
		wp_clean_theme_json_cache();
		remove_theme_mods();
		parent::tear_down();
	}

	public function test_families_are_read_from_global_settings(): void {
		$families = Fonts::families();
		$this->assertArrayHasKey( 'ubuntu', $families );
		$this->assertSame( 'Ubuntu', $families['ubuntu']['name'] );
	}

	public function test_font_stack_is_sanitized(): void {
		$this->assertSame( 'Ubuntu, sans-serif', Fonts::families()['ubuntu']['stack'] );
		$this->assertStringNotContainsString( ';', Fonts::families()['evil']['stack'] );
		$this->assertStringNotContainsString( '}', Fonts::families()['evil']['stack'] );
	}

	public function test_customizer_outputs_selected_family_stack(): void {
		$this->assertStringContainsString( '--ct-font-body:Ubuntu, sans-serif;', Customizer_Css::root_css() );
	}

	public function test_missing_family_falls_back_to_generic_stack(): void {
		set_theme_mod( 'ct_font_display', 'not-installed' );
		$field = Customizer_Schema::fields()['ct_font_display'];
		$this->assertSame( $field['default'], Customizer_Css::sanitize_value( 'not-installed', $field ) );
		$this->assertStringContainsString( '--ct-font-display:' . $field['fallback'] . ';', Customizer_Css::root_css(), 'Default (Bebas Neue) is not installed in this test.' );
	}

	public function test_preload_urls_use_regular_face_of_selected_families(): void {
		$this->assertSame( array( 'http://example.org/fonts/ubuntu-400.woff2' ), Fonts::preload_urls() );
	}
}
