<?php
/**
 * Seeds the skill-test content. Run by setup.sh via `wp eval-file`; every step is idempotent.
 *
 * @package CtCustom
 */

use CtCustom\Breadcrumbs;
use CtCustom\Settings;
use CtCustom\Setup;

defined( 'WP_CLI' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

const CT_SEED_FORM_TITLE = 'Contact us';

/**
 * Find a page by slug or create it.
 *
 * @param string $slug  Page slug.
 * @param array  $args  wp_insert_post() args for creation.
 */
function ct_seed_page( string $slug, array $args ): int {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return $page->ID;
	}
	$id = wp_insert_post(
		array_merge(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $slug,
			),
			$args
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "seed: page {$slug}: " . $id->get_error_message() );
	}
	WP_CLI::log( "seed: created page {$slug} (#{$id})" );
	return $id;
}

/**
 * Sideload a theme image into the media library.
 *
 * @param string $file  Path relative to the theme.
 * @param string $title Attachment title.
 */
function ct_seed_import_image( string $file, string $title ): int {
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! copy( get_theme_file_path( $file ), $tmp ) ) {
		WP_CLI::error( "seed: cannot copy {$file} to a temp file" );
	}
	$id = media_handle_sideload(
		array(
			'name'     => 'ct-custom-' . basename( $file ),
			'tmp_name' => $tmp,
		),
		0,
		$title
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "seed: import of {$file} failed: " . $id->get_error_message() );
	}
	WP_CLI::log( "seed: imported {$file} (#{$id})" );
	return $id;
}

/**
 * Default logo (Theme Settings), imported once.
 */
function ct_seed_logo(): int {
	$current = (int) Settings::get( 'logo_id' );
	return ( $current && wp_attachment_is_image( $current ) ) ? $current : ct_seed_import_image( 'assets/images/logo.png', 'Your Logo' );
}

/**
 * Install + activate the design fonts in the WordPress Font Library (Appearance → Fonts),
 * the same records the Fonts screen creates: wp_font_family / wp_font_face posts, files in the
 * fonts upload dir, and the family listed under settings.typography.fontFamilies.custom in global styles.
 */
function ct_seed_fonts(): void {
	$families = array(
		array(
			'name'       => 'Ubuntu',
			'slug'       => 'ubuntu',
			'fontFamily' => 'Ubuntu, sans-serif',
			'faces'      => array(
				array( '300', 'normal', 'ubuntu-300.woff2' ),
				array( '400', 'normal', 'ubuntu-400.woff2' ),
				array( '400', 'italic', 'ubuntu-400-italic.woff2' ),
				array( '700', 'normal', 'ubuntu-700.woff2' ),
			),
		),
		array(
			'name'       => 'Bebas Neue',
			'slug'       => 'bebas-neue',
			'fontFamily' => '"Bebas Neue", sans-serif',
			'faces'      => array( array( '400', 'normal', 'bebas-neue-400.woff2' ) ),
		),
	);

	$font_dir = wp_get_font_dir();
	if ( ! wp_mkdir_p( $font_dir['path'] ) ) {
		WP_CLI::error( "seed: cannot create {$font_dir['path']}" );
	}

	$activated = array();
	foreach ( $families as $family ) {
		$faces = array();
		foreach ( $family['faces'] as list( $weight, $style, $file ) ) {
			$target = trailingslashit( $font_dir['path'] ) . $file;
			if ( ! file_exists( $target ) && ! copy( __DIR__ . '/fonts/' . $file, $target ) ) {
				WP_CLI::error( "seed: cannot copy font {$file}" );
			}
			$faces[] = array(
				'fontFamily' => $family['name'],
				'fontWeight' => $weight,
				'fontStyle'  => $style,
				'src'        => trailingslashit( $font_dir['url'] ) . $file,
			);
		}

		ct_seed_font_library_post( $family, $faces );
		$activated[] = array(
			'name'       => $family['name'],
			'slug'       => $family['slug'],
			'fontFamily' => $family['fontFamily'],
			'fontFace'   => $faces,
		);
	}

	ct_seed_activate_fonts( $activated );
}

/**
 * Create one wp_font_family record through the core REST endpoint.
 *
 * @param array $family Family definition.
 * @return int Family post ID.
 */
function ct_seed_font_family( array $family ): int {
	$request = new WP_REST_Request( 'POST', '/wp/v2/font-families' );
	$request->set_param( 'font_family_settings', wp_json_encode( array_intersect_key( $family, array_flip( array( 'name', 'slug', 'fontFamily' ) ) ) ) );
	$response = rest_do_request( $request );
	if ( $response->is_error() ) {
		WP_CLI::error( "seed: font family {$family['name']}: " . $response->as_error()->get_error_message() );
	}
	return (int) $response->get_data()['id'];
}

/**
 * Create the Font Library records for one family through the core REST endpoints (once).
 *
 * @param array $family Family definition.
 * @param array $faces  Face settings with src URLs.
 */
function ct_seed_font_library_post( array $family, array $faces ): void {
	$existing = get_page_by_path( $family['slug'], OBJECT, 'wp_font_family' );
	if ( $existing && get_children( array( 'post_parent' => $existing->ID, 'post_type' => 'wp_font_face' ) ) ) {
		return;
	}

	// A family left without faces by an interrupted run gets its faces added instead of being skipped forever.
	$family_id = $existing ? $existing->ID : ct_seed_font_family( $family );

	foreach ( $faces as $face ) {
		$request = new WP_REST_Request( 'POST', "/wp/v2/font-families/{$family_id}/font-faces" );
		$request->set_param( 'font_face_settings', wp_json_encode( $face ) );
		$response = rest_do_request( $request );
		if ( $response->is_error() ) {
			WP_CLI::error( "seed: font face {$family['name']} {$face['fontWeight']}: " . $response->as_error()->get_error_message() );
		}
	}
	WP_CLI::log( "seed: installed font {$family['name']}" );
}

/**
 * Mark families active in the user's global styles (what the Fonts screen toggles).
 *
 * @param array $families Families with fontFace data.
 */
function ct_seed_activate_fonts( array $families ): void {
	$post = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), true );
	if ( empty( $post['ID'] ) ) {
		WP_CLI::error( 'seed: cannot load the global styles post' );
	}

	$data   = json_decode( $post['post_content'], true );
	$data   = is_array( $data ) ? $data : array();
	$active = $data['settings']['typography']['fontFamilies']['custom'] ?? array();
	$slugs  = array_column( $active, 'slug' );

	foreach ( $families as $family ) {
		if ( ! in_array( $family['slug'], $slugs, true ) ) {
			$active[] = $family;
		}
	}

	$data['version']                                             = 3;
	$data['isGlobalStylesUserThemeJSON']                         = true;
	$data['settings']['typography']['fontFamilies']['custom']   = $active;

	$updated = wp_update_post(
		array(
			'ID'           => $post['ID'],
			'post_content' => wp_slash( wp_json_encode( $data ) ),
		),
		true
	);
	if ( is_wp_error( $updated ) ) {
		WP_CLI::error( 'seed: activating fonts failed: ' . $updated->get_error_message() );
	}
	wp_clean_theme_json_cache();
}

/**
 * Create the Contact Form 7 form matching the design.
 */
function ct_seed_contact_form(): void {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		WP_CLI::error( 'seed: Contact Form 7 is not active' );
	}
	if ( WPCF7_ContactForm::find( array( 'title' => CT_SEED_FORM_TITLE ) ) ) {
		return;
	}

	$form = WPCF7_ContactForm::get_template( array( 'title' => CT_SEED_FORM_TITLE ) );
	$mail = $form->prop( 'mail' );

	$mail['subject'] = '[_site_title] New message from [your-name]';
	$mail['body']    = "Name: [your-name]\nPhone: [your-phone]\nEmail: [your-email]\n\n[your-message]\n\n-- \nSent from [_site_url]";
	$mail['additional_headers'] = 'Reply-To: [your-email]';

	$form->set_properties(
		array(
			'form' => implode(
				"\n",
				array(
					'<div class="ct-form">',
					'<p class="ct-form__row"><label class="screen-reader-text" for="ct-name">Name</label>[text* your-name id:ct-name autocomplete:name placeholder "Name *"]</p>',
					'<div class="ct-form__row ct-form__row--split">',
					'<p><label class="screen-reader-text" for="ct-phone">Phone</label>[tel* your-phone id:ct-phone autocomplete:tel placeholder "Phone *"]</p>',
					'<p><label class="screen-reader-text" for="ct-email">Email</label>[email* your-email id:ct-email autocomplete:email placeholder "Email *"]</p>',
					'</div>',
					'<p class="ct-form__row"><label class="screen-reader-text" for="ct-message">Message</label>[textarea* your-message id:ct-message placeholder "Message *"]</p>',
					'<p class="ct-form__row ct-form__row--submit">[submit "Submit"]</p>',
					'</div>',
				)
			),
			'mail' => $mail,
		)
	);
	$form->save();
	WP_CLI::log( 'seed: created Contact Form 7 form' );
}

/**
 * Homepage content: intro paragraph + the "Contact section" pattern, expanded so it is editable.
 */
function ct_seed_homepage_content(): string {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'ct-custom/contact-section' );
	if ( ! $pattern ) {
		WP_CLI::error( 'seed: pattern ct-custom/contact-section is not registered' );
	}

	$intro = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam posuere ipsum nec velit mattis elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas eu placerat metus, eget placerat libero.';

	return "<!-- wp:paragraph {\"className\":\"is-style-ct-intro\"} -->\n<p class=\"is-style-ct-intro\">{$intro}</p>\n<!-- /wp:paragraph -->\n\n" . trim( $pattern['content'] );
}

/**
 * Three-level menu from the design. Title 2 → Submenu 3 → Submenu 3 points at the homepage,
 * so WordPress marks that branch current/ancestor (bold + underline, as in the PSD).
 *
 * @param int $homepage_id Front page ID.
 */
function ct_seed_menu( int $homepage_id ): void {
	$menu = wp_get_nav_menu_object( 'Main Menu' );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( 'Main Menu' );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( 'seed: menu: ' . $id->get_error_message() );
	}

	if ( ! wp_get_nav_menu_items( $id ) ) {
		$add = static function ( string $title, int $parent = 0, int $page_id = 0 ) use ( $id ): int {
			$args = array(
				'menu-item-title'     => $title,
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			);
			$args += $page_id
				? array(
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_id,
				)
				: array(
					'menu-item-type' => 'custom',
					'menu-item-url'  => '#',
				);
			$item = wp_update_nav_menu_item( $id, 0, $args );
			if ( is_wp_error( $item ) ) {
				WP_CLI::error( "seed: menu item {$title}: " . $item->get_error_message() );
			}
			return $item;
		};

		foreach ( range( 1, 7 ) as $n ) {
			$top = $add( "Title {$n}" );
			if ( 2 !== $n ) {
				continue;
			}
			$add( 'Submenu 1', $top );
			$add( 'Submenu 2', $top );
			$sub = $add( 'Submenu 3', $top );
			$add( 'Submenu 1', $sub );
			$add( 'Submenu 2', $sub );
			$add( 'Submenu 3', $sub, $homepage_id );
		}
		WP_CLI::log( 'seed: created Main Menu' );
	}

	$locations                        = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ Setup::MENU_PRIMARY ] = $id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

// Site options.
update_option( 'blogname', 'Coalition Skills Test' );
update_option( 'blogdescription', 'WordPress skill test' );
update_option( 'default_role', 'subscriber' );
// Local dev site: skip the periodic "confirm admin email" screen after login.
update_option( 'admin_email_lifespan', time() + 10 * YEAR_IN_SECONDS );

// Theme Settings (keep anything an editor already changed).
$ct_first_run = false === get_option( Settings::OPTION );
$ct_settings  = array_merge( Settings::defaults(), (array) get_option( Settings::OPTION, array() ) );
// Demo links only on the first run: an editor who removes every link keeps it that way.
if ( $ct_first_run ) {
	// Top-bar Signup link needs registration on; an admin who turns it off keeps it off.
	update_option( 'users_can_register', 1 );
	$ct_settings['socials'] = array(
		array( 'name' => 'Facebook', 'url' => 'https://www.facebook.com/' ),
		array( 'name' => 'Twitter', 'url' => 'https://twitter.com/' ),
		array( 'name' => 'LinkedIn', 'url' => 'https://www.linkedin.com/' ),
		array( 'name' => 'Pinterest', 'url' => 'https://www.pinterest.com/' ),
	);
}
$ct_settings['logo_id'] = ct_seed_logo();

// SMTP from .env (MAIL_*), only while Theme Settings → Email is still unconfigured.
if ( '' === (string) ( $ct_settings['mail_host'] ?? '' ) && 'smtp' === getenv( 'MAIL_MAILER' ) && getenv( 'MAIL_HOST' ) ) {
	$ct_settings = array_merge(
		$ct_settings,
		array(
			'mail_enabled'    => '1',
			'mail_host'       => (string) getenv( 'MAIL_HOST' ),
			'mail_port'       => (string) getenv( 'MAIL_PORT' ),
			'mail_encryption' => (string) getenv( 'MAIL_ENCRYPTION' ),
			'mail_username'   => (string) getenv( 'MAIL_USERNAME' ),
			'mail_password'   => (string) getenv( 'MAIL_PASSWORD' ),
			'mail_from_email' => (string) getenv( 'MAIL_FROM_ADDRESS' ),
			'mail_from_name'  => (string) getenv( 'MAIL_FROM_NAME' ),
		)
	);
	WP_CLI::log( 'seed: SMTP settings imported from .env' );
}
update_option( Settings::OPTION, Settings::sanitize( $ct_settings ) );

ct_seed_fonts();
ct_seed_contact_form();

// Pages.
$ct_who_id  = ct_seed_page( 'who-we-are', array( 'post_title' => 'Who we are', 'post_content' => '<!-- wp:paragraph --><p>About Coalition Skills Test.</p><!-- /wp:paragraph -->' ) );
$ct_home_id = ct_seed_page(
	'homepage',
	array(
		'post_title'   => 'Homepage',
		'post_content' => ct_seed_homepage_content(),
	)
);
// Demo page setup is written once; later runs keep whatever an editor changed.
$ct_home_meta = array(
	'_wp_page_template'      => 'homepage.php',
	Breadcrumbs::META_TITLE  => 'Contact',
	Breadcrumbs::META_PARENT => $ct_who_id,
);
foreach ( $ct_home_meta as $ct_key => $ct_value ) {
	if ( ! metadata_exists( 'post', $ct_home_id, $ct_key ) ) {
		update_post_meta( $ct_home_id, $ct_key, $ct_value );
	}
}

if ( $ct_first_run ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ct_home_id );
}

ct_seed_menu( $ct_home_id );

WP_CLI::success( 'seed: content ready' );
