<?php
/**
 * Declarative list of every design token exposed in the Customizer.
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Sections and fields. Each field becomes a theme_mod, a Customizer control and a CSS custom property.
 */
final class Customizer_Schema {

	/**
	 * Section id => title.
	 *
	 * @return array<string, string>
	 */
	public static function sections(): array {
		return array(
			'ct_global'     => __( 'Global colors & layout', 'ct-custom' ),
			'ct_typography' => __( 'Typography', 'ct-custom' ),
			'ct_topbar'     => __( 'Top bar', 'ct-custom' ),
			'ct_header'     => __( 'Header & logo', 'ct-custom' ),
			'ct_menu'       => __( 'Main menu', 'ct-custom' ),
			'ct_submenu'    => __( 'Sub menu', 'ct-custom' ),
			'ct_subsubmenu' => __( 'Sub-sub menu', 'ct-custom' ),
			'ct_content'    => __( 'Breadcrumbs & borders', 'ct-custom' ),
			'ct_forms'      => __( 'Forms & buttons', 'ct-custom' ),
			'ct_social'     => __( 'Social icons', 'ct-custom' ),
			'ct_footer'     => __( 'Footer', 'ct-custom' ),
		);
	}

	/**
	 * All fields, keyed by theme_mod id. `var` is derived from the id (ct_h1_size → --ct-h1-size).
	 *
	 * @return array<string, array{section: string, label: string, type: string, default: int|string, var: string, min?: int, max?: int, step?: int, unit?: string}>
	 */
	public static function fields(): array {
		static $fields = null;
		if ( null !== $fields ) {
			return $fields;
		}

		$c = static fn( string $section, string $label, string $fallback ): array => array(
			'section' => $section,
			'label'   => $label,
			'type'    => 'color',
			'default' => $fallback,
		);
		$r = static fn( string $section, string $label, int $fallback, int $min, int $max ): array => array(
			'section' => $section,
			'label'   => $label,
			'type'    => 'range',
			'default' => $fallback,
			'min'     => $min,
			'max'     => $max,
			'step'    => 1,
			'unit'    => 'px',
		);

		$f = static fn( string $label, string $fallback_slug, string $generic ): array => array(
			'section'  => 'ct_typography',
			'label'    => $label,
			'type'     => 'font',
			'default'  => $fallback_slug,
			'fallback' => $generic,
		);

		$fields = array(
			// Fonts: families installed in Appearance → Fonts.
			'ct_font_body'                => $f( __( 'Body font', 'ct-custom' ), 'ubuntu', '"Trebuchet MS", sans-serif' ),
			'ct_font_display'             => $f( __( 'Display font (logo, top bar)', 'ct-custom' ), 'bebas-neue', 'Impact, "Arial Narrow", sans-serif' ),
			// Global.
			'ct_color_primary'            => $c( 'ct_global', __( 'Primary (accent)', 'ct-custom' ), '#ff6200' ),
			'ct_color_heading'            => $c( 'ct_global', __( 'Headings', 'ct-custom' ), '#ff6200' ),
			'ct_color_text'               => $c( 'ct_global', __( 'Body text', 'ct-custom' ), '#595959' ),
			'ct_color_muted'              => $c( 'ct_global', __( 'Muted text (intro)', 'ct-custom' ), '#878787' ),
			'ct_color_link'               => $c( 'ct_global', __( 'Links', 'ct-custom' ), '#ff6200' ),
			'ct_color_bg'                 => $c( 'ct_global', __( 'Page background', 'ct-custom' ), '#ffffff' ),
			'ct_container_width'          => $r( 'ct_global', __( 'Container width', 'ct-custom' ), 960, 720, 1600 ),
			// Typography.
			'ct_font_size_base'           => $r( 'ct_typography', __( 'Body font size', 'ct-custom' ), 14, 10, 24 ),
			'ct_line_height_base'         => $r( 'ct_typography', __( 'Body line height', 'ct-custom' ), 19, 12, 40 ),
			'ct_h1_size'                  => $r( 'ct_typography', __( 'H1 size', 'ct-custom' ), 30, 16, 72 ),
			'ct_h2_size'                  => $r( 'ct_typography', __( 'H2 size', 'ct-custom' ), 23, 14, 60 ),
			'ct_h3_size'                  => $r( 'ct_typography', __( 'H3 size', 'ct-custom' ), 18, 12, 48 ),
			'ct_h4_size'                  => $r( 'ct_typography', __( 'H4 size', 'ct-custom' ), 16, 12, 40 ),
			// Top bar.
			'ct_topbar_bg'                => $c( 'ct_topbar', __( 'Background', 'ct-custom' ), '#ff6200' ),
			'ct_topbar_label_color'       => $c( 'ct_topbar', __( 'Label color ("Call us now!", Login)', 'ct-custom' ), '#be3600' ),
			'ct_topbar_text_color'        => $c( 'ct_topbar', __( 'Highlight color (phone, Signup)', 'ct-custom' ), '#ffffff' ),
			'ct_topbar_height'            => $r( 'ct_topbar', __( 'Height', 'ct-custom' ), 34, 24, 80 ),
			'ct_topbar_font_size'         => $r( 'ct_topbar', __( 'Font size', 'ct-custom' ), 19, 10, 32 ),
			// Header.
			'ct_header_bg'                => $c( 'ct_header', __( 'Background', 'ct-custom' ), '#f9f9f9' ),
			'ct_header_height'            => $r( 'ct_header', __( 'Height', 'ct-custom' ), 109, 60, 200 ),
			'ct_logo_height'              => $r( 'ct_header', __( 'Logo height', 'ct-custom' ), 38, 20, 120 ),
			// Main menu.
			'ct_menu_color'               => $c( 'ct_menu', __( 'Text', 'ct-custom' ), '#333333' ),
			'ct_menu_hover_color'         => $c( 'ct_menu', __( 'Text on hover', 'ct-custom' ), '#ff6200' ),
			'ct_menu_active_border_color' => $c( 'ct_menu', __( 'Active underline', 'ct-custom' ), '#ff6200' ),
			'ct_menu_font_size'           => $r( 'ct_menu', __( 'Font size', 'ct-custom' ), 12, 10, 24 ),
			'ct_menu_item_gap'            => $r( 'ct_menu', __( 'Space between items', 'ct-custom' ), 22, 0, 80 ),
			'ct_menu_border_width'        => $r( 'ct_menu', __( 'Active underline thickness', 'ct-custom' ), 4, 0, 12 ),
			// Sub menu.
			'ct_submenu_bg'               => $c( 'ct_submenu', __( 'Background', 'ct-custom' ), '#ffffff' ),
			'ct_submenu_border_color'     => $c( 'ct_submenu', __( 'Border', 'ct-custom' ), '#a1a1a1' ),
			'ct_submenu_color'            => $c( 'ct_submenu', __( 'Text', 'ct-custom' ), '#454545' ),
			'ct_submenu_hover_color'      => $c( 'ct_submenu', __( 'Text on hover', 'ct-custom' ), '#ff6200' ),
			'ct_submenu_font_size'        => $r( 'ct_submenu', __( 'Font size', 'ct-custom' ), 13, 10, 24 ),
			'ct_submenu_line_height'      => $r( 'ct_submenu', __( 'Item height', 'ct-custom' ), 24, 16, 60 ),
			// Sub-sub menu.
			'ct_subsubmenu_bg'            => $c( 'ct_subsubmenu', __( 'Background', 'ct-custom' ), '#ffffff' ),
			'ct_subsubmenu_border_color'  => $c( 'ct_subsubmenu', __( 'Border', 'ct-custom' ), '#a1a1a1' ),
			'ct_subsubmenu_color'         => $c( 'ct_subsubmenu', __( 'Text', 'ct-custom' ), '#454545' ),
			'ct_subsubmenu_hover_color'   => $c( 'ct_subsubmenu', __( 'Text on hover', 'ct-custom' ), '#ff6200' ),
			'ct_subsubmenu_font_size'     => $r( 'ct_subsubmenu', __( 'Font size', 'ct-custom' ), 13, 10, 24 ),
			// Breadcrumbs & borders.
			'ct_breadcrumb_color'         => $c( 'ct_content', __( 'Breadcrumb links', 'ct-custom' ), '#adadad' ),
			'ct_breadcrumb_current_color' => $c( 'ct_content', __( 'Breadcrumb current page', 'ct-custom' ), '#6a6a6a' ),
			'ct_breadcrumb_font_size'     => $r( 'ct_content', __( 'Breadcrumb font size', 'ct-custom' ), 14, 10, 24 ),
			'ct_border_color'             => $c( 'ct_content', __( 'Section heading border', 'ct-custom' ), '#525252' ),
			'ct_border_width'             => $r( 'ct_content', __( 'Section heading border thickness', 'ct-custom' ), 6, 0, 20 ),
			// Forms.
			'ct_input_bg'                 => $c( 'ct_forms', __( 'Field background', 'ct-custom' ), '#f6f6f6' ),
			'ct_input_border_color'       => $c( 'ct_forms', __( 'Field border', 'ct-custom' ), '#ededed' ),
			'ct_input_text_color'         => $c( 'ct_forms', __( 'Field text', 'ct-custom' ), '#595959' ),
			'ct_input_placeholder_color'  => $c( 'ct_forms', __( 'Placeholder', 'ct-custom' ), '#878787' ),
			'ct_button_bg'                => $c( 'ct_forms', __( 'Button background', 'ct-custom' ), '#ff6200' ),
			'ct_button_hover_bg'          => $c( 'ct_forms', __( 'Button background on hover', 'ct-custom' ), '#be3600' ),
			'ct_button_color'             => $c( 'ct_forms', __( 'Button text', 'ct-custom' ), '#ffffff' ),
			// Social icons.
			'ct_social_bg'                => $c( 'ct_social', __( 'Icon background', 'ct-custom' ), '#d0d0d0' ),
			'ct_social_hover_bg'          => $c( 'ct_social', __( 'Icon background on hover', 'ct-custom' ), '#363636' ),
			'ct_social_color'             => $c( 'ct_social', __( 'Icon color', 'ct-custom' ), '#ffffff' ),
			'ct_social_size'              => $r( 'ct_social', __( 'Icon box size', 'ct-custom' ), 31, 20, 64 ),
			// Footer.
			'ct_footer_bg'                => $c( 'ct_footer', __( 'Background', 'ct-custom' ), '#f9f9f9' ),
			'ct_footer_color'             => $c( 'ct_footer', __( 'Text', 'ct-custom' ), '#878787' ),
		);

		foreach ( $fields as $id => &$field ) {
			$field['var'] = '--ct-' . str_replace( '_', '-', substr( $id, 3 ) );
		}
		unset( $field );

		return $fields;
	}
}
