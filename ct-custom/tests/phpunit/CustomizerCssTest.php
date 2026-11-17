<?php
/**
 * Customizer → CSS custom properties tests.
 *
 * @package CtCustom
 */

use CtCustom\Customizer_Schema;
use CtCustom\Customizer_Css;

/**
 * @covers \CtCustom\Customizer_Css
 * @covers \CtCustom\Customizer_Schema
 */
class CustomizerCssTest extends WP_UnitTestCase {

	public function tear_down(): void {
		remove_theme_mods();
		parent::tear_down();
	}

	public function test_every_schema_field_has_required_keys(): void {
		foreach ( Customizer_Schema::fields() as $id => $field ) {
			$this->assertMatchesRegularExpression( '/^ct_[a-z0-9_]+$/', $id );
			$this->assertContains( $field['type'], array( 'color', 'range', 'font' ), $id );
			$this->assertArrayHasKey( 'var', $field, $id );
			$this->assertArrayHasKey( 'default', $field, $id );
			$this->assertArrayHasKey( 'section', $field, $id );
			if ( 'range' === $field['type'] ) {
				$this->assertGreaterThanOrEqual( $field['min'], $field['default'], $id );
				$this->assertLessThanOrEqual( $field['max'], $field['default'], $id );
			}
		}
	}

	public function test_css_vars_are_unique(): void {
		$vars = array_column( Customizer_Schema::fields(), 'var' );
		$this->assertSame( count( $vars ), count( array_unique( $vars ) ) );
	}

	public function test_root_css_outputs_defaults(): void {
		$css = Customizer_Css::root_css();
		$this->assertStringStartsWith( ':root{', $css );
		$this->assertStringContainsString( '--ct-color-primary:#ff6200;', $css );
		$this->assertStringContainsString( '--ct-h1-size:30px;', $css );
	}

	public function test_root_css_uses_theme_mod(): void {
		set_theme_mod( 'ct_color_primary', '#123abc' );
		$this->assertStringContainsString( '--ct-color-primary:#123abc;', Customizer_Css::root_css() );
	}

	public function test_invalid_stored_values_fall_back_to_defaults(): void {
		set_theme_mod( 'ct_color_primary', 'red;}body{display:none' );
		set_theme_mod( 'ct_h1_size', 9999 );
		$css = Customizer_Css::root_css();
		$this->assertStringContainsString( '--ct-color-primary:#ff6200;', $css );
		$this->assertStringContainsString( '--ct-h1-size:30px;', $css );
		$this->assertStringNotContainsString( 'display:none', $css );
	}

	public function test_sanitize_range_clamps_to_field_bounds(): void {
		$field = Customizer_Schema::fields()['ct_h1_size'];
		$this->assertSame( $field['default'], Customizer_Css::sanitize_value( 'abc', $field ) );
		$this->assertSame( $field['default'], Customizer_Css::sanitize_value( $field['max'] + 1, $field ) );
		$this->assertSame( $field['min'], Customizer_Css::sanitize_value( (string) $field['min'], $field ) );
	}

	public function test_sanitize_color_accepts_hex_only(): void {
		$field = Customizer_Schema::fields()['ct_color_primary'];
		$this->assertSame( '#abcdef', Customizer_Css::sanitize_value( '#abcdef', $field ) );
		$this->assertSame( '#ff6200', Customizer_Css::sanitize_value( 'rgb(0,0,0)', $field ) );
	}
}
