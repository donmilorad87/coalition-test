<?php
/**
 * Customizer panel registration and live preview.
 *
 * @package CtCustom
 */

namespace CtCustom;

use WP_Customize_Color_Control;
use WP_Customize_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the "Theme design" panel from Customizer_Schema plus a few content toggles.
 */
final class Customizer {

	private const PANEL = 'ct_design';

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'customize_register', array( self::class, 'register' ) );
		add_action( 'customize_preview_init', array( self::class, 'enqueue_preview' ) );
	}

	/**
	 * Non-CSS options (theme_mod => default).
	 *
	 * @return array<string, string|bool>
	 */
	public static function option_defaults(): array {
		return array(
			'ct_topbar_label' => __( 'Call us now!', 'ct-custom' ),
			'ct_show_footer'  => true,
		);
	}

	/**
	 * Read a non-CSS option.
	 *
	 * @param string $id theme_mod id.
	 * @return string|bool
	 */
	public static function option( string $id ): string|bool {
		return get_theme_mod( $id, self::option_defaults()[ $id ] ?? '' );
	}

	/**
	 * Register panel, sections, settings and controls.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public static function register( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_panel(
			self::PANEL,
			array(
				'title'       => __( 'Theme design', 'ct-custom' ),
				'description' => __( 'Colors, sizes and spacing for every part of the theme. Changes preview live.', 'ct-custom' ),
				'priority'    => 30,
			)
		);

		foreach ( Customizer_Schema::sections() as $id => $title ) {
			$wp_customize->add_section(
				$id,
				array(
					'title' => $title,
					'panel' => self::PANEL,
				)
			);
		}

		foreach ( Customizer_Schema::fields() as $id => $field ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $field['default'],
					'transport'         => 'postMessage',
					'sanitize_callback' => static fn( $value ) => Customizer_Css::sanitize_value( $value, $field ),
				)
			);

			if ( 'font' === $field['type'] ) {
				$wp_customize->add_control(
					$id,
					array(
						'label'       => $field['label'],
						'description' => __( 'Install and activate fonts in Appearance → Fonts.', 'ct-custom' ),
						'section'     => $field['section'],
						'type'        => 'select',
						'choices'     => wp_list_pluck( Fonts::families(), 'name' ),
					)
				);
				continue;
			}

			if ( 'color' === $field['type'] ) {
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize,
						$id,
						array(
							'label'   => $field['label'],
							'section' => $field['section'],
						)
					)
				);
				continue;
			}

			$wp_customize->add_control(
				$id,
				array(
					'label'       => $field['label'] . ' (' . $field['unit'] . ')',
					'section'     => $field['section'],
					'type'        => 'number',
					'input_attrs' => array(
						'min'  => $field['min'],
						'max'  => $field['max'],
						'step' => $field['step'],
					),
				)
			);
		}

		self::register_options( $wp_customize );
	}

	/**
	 * Content options that need a refresh (not CSS variables).
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function register_options( WP_Customize_Manager $wp_customize ): void {
		$defaults = self::option_defaults();

		$wp_customize->add_setting(
			'ct_topbar_label',
			array(
				'default'           => $defaults['ct_topbar_label'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'ct_topbar_label',
			array(
				'label'   => __( 'Phone label', 'ct-custom' ),
				'section' => 'ct_topbar',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'ct_show_footer',
			array(
				'default'           => $defaults['ct_show_footer'],
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		$wp_customize->add_control(
			'ct_show_footer',
			array(
				'label'   => __( 'Show footer', 'ct-custom' ),
				'section' => 'ct_footer',
				'type'    => 'checkbox',
			)
		);
	}

	/**
	 * Live preview: setting id → CSS var map consumed by src/customizer/preview.js.
	 */
	public static function enqueue_preview(): void {
		$map = array();
		foreach ( Customizer_Schema::fields() as $id => $field ) {
			$map[ $id ] = array(
				'cssVar' => $field['var'],
				'unit'   => $field['unit'] ?? '',
			);
			if ( 'font' === $field['type'] ) {
				$map[ $id ]['values'] = wp_list_pluck( Fonts::families(), 'stack' );
			}
		}

		$handle = Assets::enqueue_build_script( 'customizer-preview', array( 'customize-preview' ) );
		wp_add_inline_script( $handle, 'window.ctCustomizerVars = ' . wp_json_encode( $map ) . ';', 'before' );
	}
}
