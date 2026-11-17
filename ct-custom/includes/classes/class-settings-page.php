<?php
/**
 * Appearance → Theme Settings admin page (Settings API, no plugins).
 *
 * @package CtCustom
 */

namespace CtCustom;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and registers the Theme Settings page.
 */
final class Settings_Page {

	public const SLUG   = 'ct-custom-settings';
	private const GROUP = 'ct_custom_settings_group';

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'admin_menu', array( self::class, 'add_page' ) );
		add_action( 'admin_init', array( self::class, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	/**
	 * Add the page under Appearance.
	 */
	public static function add_page(): void {
		add_theme_page(
			__( 'Theme Settings', 'ct-custom' ),
			__( 'Theme Settings', 'ct-custom' ),
			'manage_options',
			self::SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Field definitions grouped by section: section => [ title, fields[ key => [ label, type ] ] ].
	 *
	 * @return array<string, array{title: string, fields: array<string, array{0: string, 1: string}>}>
	 */
	private static function sections(): array {
		return array(
			'ct_branding' => array(
				'title'  => __( 'Branding', 'ct-custom' ),
				'fields' => array( 'logo_id' => array( __( 'Logo', 'ct-custom' ), 'image' ) ),
			),
			'ct_contact'  => array(
				'title'  => __( 'Contact information', 'ct-custom' ),
				'fields' => array(
					'company' => array( __( 'Company name', 'ct-custom' ), 'text' ),
					'address' => array( __( 'Address', 'ct-custom' ), 'textarea' ),
					'phone'   => array( __( 'Phone number', 'ct-custom' ), 'tel' ),
					'fax'     => array( __( 'Fax number', 'ct-custom' ), 'tel' ),
				),
			),
			'ct_social'   => array(
				'title'  => __( 'Social media links', 'ct-custom' ),
				'fields' => array( 'socials' => array( __( 'Links', 'ct-custom' ), 'socials' ) ),
			),
			'ct_mail'     => array(
				'title'  => __( 'Email (SMTP)', 'ct-custom' ),
				'fields' => array(
					'mail_enabled'    => array( __( 'Send email via SMTP', 'ct-custom' ), 'checkbox' ),
					'mail_host'       => array( __( 'SMTP host', 'ct-custom' ), 'text' ),
					'mail_port'       => array( __( 'SMTP port', 'ct-custom' ), 'number' ),
					'mail_encryption' => array( __( 'Encryption', 'ct-custom' ), 'encryption' ),
					'mail_username'   => array( __( 'Username', 'ct-custom' ), 'text' ),
					'mail_password'   => array( __( 'Password', 'ct-custom' ), 'password' ),
					'mail_from_email' => array( __( 'From email', 'ct-custom' ), 'email' ),
					'mail_from_name'  => array( __( 'From name', 'ct-custom' ), 'text' ),
				),
			),
		);
	}

	/**
	 * Register the option, sections and fields.
	 */
	public static function register(): void {
		register_setting(
			self::GROUP,
			Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Settings::class, 'sanitize' ),
				'default'           => Settings::defaults(),
			)
		);

		foreach ( self::sections() as $section_id => $section ) {
			add_settings_section( $section_id, $section['title'], '__return_false', self::SLUG );

			foreach ( $section['fields'] as $key => list( $label, $type ) ) {
				add_settings_field(
					'ct_' . $key,
					$label,
					array( self::class, 'render_field' ),
					self::SLUG,
					$section_id,
					array(
						'key'       => $key,
						'type'      => $type,
						'label_for' => 'ct_' . $key,
					)
				);
			}
		}
	}

	/**
	 * Media uploader + page script, only on this screen.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public static function enqueue( string $hook_suffix ): void {
		if ( 'appearance_page_' . self::SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_media();
		Assets::enqueue_build_script( 'admin-settings' );
		wp_add_inline_style(
			'wp-admin',
			'.ct-media-field__preview img{max-height:80px;width:auto;margin-bottom:8px}.ct-media-field .button-link-delete{margin-left:8px}'
			. '.ct-socials__list{margin:0 0 12px;max-width:900px}.ct-socials__row{display:grid;grid-template-columns:150px 1fr 1.5fr auto;gap:12px;align-items:end;margin:0 0 8px;padding:12px;background:#fff;border:1px solid #dcdcde}'
			. '.ct-socials__row label{display:block;font-weight:600}.ct-socials__row input{width:100%;margin-top:4px}.ct-socials__row .ct-media-field__preview img{max-height:32px}'
			. '@media (max-width:782px){.ct-socials__row{grid-template-columns:1fr}}'
		);
	}

	/**
	 * Render one field.
	 *
	 * @param array{key: string, type: string, label_for: string} $args Field args.
	 */
	public static function render_field( array $args ): void {
		$key   = $args['key'];
		$id    = $args['label_for'];
		$name  = Settings::OPTION . '[' . $key . ']';
		$value = Settings::get( $key );

		switch ( $args['type'] ) {
			case 'image':
				self::render_image_field( $id, $name, (int) $value, 'medium' );
				break;
			case 'socials':
				self::render_socials_field( $name );
				break;
			case 'checkbox':
				printf(
					'<label><input id="%1$s" name="%2$s" type="checkbox" value="1"%3$s> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( (bool) $value, true, false ),
					esc_html__( 'Use the server below for all site email (contact form, notifications).', 'ct-custom' )
				);
				break;
			case 'encryption':
				$options = array(
					'tls' => __( 'TLS (STARTTLS)', 'ct-custom' ),
					'ssl' => __( 'SSL', 'ct-custom' ),
					''    => __( 'None', 'ct-custom' ),
				);
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $options as $option => $label ) {
					printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $option ), selected( (string) $value, $option, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;
			case 'password':
				// Never echo the saved password back into the page.
				printf(
					'<input id="%1$s" name="%2$s" type="password" value="" class="regular-text" autocomplete="new-password" placeholder="%3$s">',
					esc_attr( $id ),
					esc_attr( $name ),
					'' !== (string) $value ? esc_attr__( 'Saved — leave blank to keep', 'ct-custom' ) : ''
				);
				break;
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( (string) $value )
				);
				break;
			default:
				printf(
					'<input id="%1$s" name="%2$s" type="%3$s" value="%4$s" class="regular-text">',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $args['type'] ),
					esc_attr( (string) $value )
				);
		}
	}

	/**
	 * Image picker: hidden ID input + preview + select/remove buttons (wired up in src/admin/settings.js).
	 *
	 * @param string $id       Input id.
	 * @param string $name     Input name.
	 * @param int    $image_id Current attachment ID.
	 * @param string $size     Preview image size.
	 */
	private static function render_image_field( string $id, string $name, int $image_id, string $size ): void {
		?>
		<div class="ct-media-field" data-ct-media-field>
			<div class="ct-media-field__preview" data-ct-media-preview>
				<?php echo $image_id ? wp_get_attachment_image( $image_id, $size ) : ''; ?>
			</div>
			<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $image_id ); ?>" data-ct-media-input>
			<button type="button" class="button" data-ct-media-select data-title="<?php esc_attr_e( 'Select image', 'ct-custom' ); ?>"><?php esc_html_e( 'Select image', 'ct-custom' ); ?></button>
			<button type="button" class="button-link button-link-delete" data-ct-media-remove <?php echo $image_id ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove', 'ct-custom' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Repeatable social link rows (image, name, URL) + a <template> used by settings.js to add rows.
	 *
	 * @param string $name Base input name (option[socials]).
	 */
	private static function render_socials_field( string $name ): void {
		?>
		<div class="ct-socials" data-ct-socials>
			<ol class="ct-socials__list" data-ct-socials-list>
				<?php
				foreach ( Settings::socials() as $index => $row ) {
					self::render_social_row( $name, (string) $index, $row );
				}
				?>
			</ol>
			<template data-ct-socials-template>
				<?php self::render_social_row( $name, '__index__', array() ); ?>
			</template>
			<button type="button" class="button" data-ct-socials-add><?php esc_html_e( 'Add social link', 'ct-custom' ); ?></button>
			<p class="description">
				<?php esc_html_e( 'Rows without a name or URL are ignored. Facebook, Twitter, LinkedIn and Pinterest get a built-in icon when no image is set.', 'ct-custom' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * One social link row.
	 *
	 * @param string $name  Base input name.
	 * @param string $index Row index (or the template placeholder).
	 * @param array  $row   Row values.
	 */
	private static function render_social_row( string $name, string $index, array $row ): void {
		$base = $name . '[' . $index . ']';
		$id   = 'ct_social_' . $index;
		?>
		<li class="ct-socials__row" data-ct-socials-row>
			<?php self::render_image_field( $id . '_image', $base . '[image_id]', (int) ( $row['image_id'] ?? 0 ), 'thumbnail' ); ?>
			<label for="<?php echo esc_attr( $id . '_name' ); ?>">
				<?php esc_html_e( 'Name', 'ct-custom' ); ?>
				<input type="text" id="<?php echo esc_attr( $id . '_name' ); ?>" name="<?php echo esc_attr( $base . '[name]' ); ?>" value="<?php echo esc_attr( $row['name'] ?? '' ); ?>">
			</label>
			<label for="<?php echo esc_attr( $id . '_url' ); ?>">
				<?php esc_html_e( 'URL', 'ct-custom' ); ?>
				<input type="url" id="<?php echo esc_attr( $id . '_url' ); ?>" name="<?php echo esc_attr( $base . '[url]' ); ?>" value="<?php echo esc_url( $row['url'] ?? '' ); ?>" placeholder="https://">
			</label>
			<button type="button" class="button-link button-link-delete" data-ct-socials-remove><?php esc_html_e( 'Remove link', 'ct-custom' ); ?></button>
		</li>
		<?php
	}

	/**
	 * Result notice after "Send test email" (see Mailer::send_test()).
	 */
	private static function render_mail_test_notice(): void {
		$result = isset( $_GET['ct-mail-test'] ) ? sanitize_key( wp_unslash( $_GET['ct-mail-test'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		if ( 'sent' === $result ) {
			wp_admin_notice(
				__( 'Test email sent.', 'ct-custom' ),
				array(
					'type'        => 'success',
					'dismissible' => true,
				)
			);
		} elseif ( 'failed' === $result ) {
			wp_admin_notice(
				__( 'Test email failed. Check the SMTP settings; the error is in wp-content/debug.log.', 'ct-custom' ),
				array(
					'type'        => 'error',
					'dismissible' => true,
				)
			);
		}
	}

	/**
	 * Page markup.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'These values feed the header, the top bar and the Reach Us / Social Links blocks (blocks can override them per instance).', 'ct-custom' ); ?></p>
			<?php
			settings_errors(); // Appearance sub-pages don't print the "Settings saved" notice themselves.
			self::render_mail_test_notice();
			?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::GROUP );
				do_settings_sections( self::SLUG );
				submit_button();
				?>
			</form>

			<h2><?php esc_html_e( 'Test email', 'ct-custom' ); ?></h2>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="<?php echo esc_attr( Mailer::TEST_ACTION ); ?>">
				<?php wp_nonce_field( Mailer::TEST_ACTION ); ?>
				<p>
					<?php
					/* translators: %s: current user's email address. */
					echo esc_html( sprintf( __( 'Save your settings first, then send a test message to %s.', 'ct-custom' ), wp_get_current_user()->user_email ) );
					?>
				</p>
				<?php submit_button( __( 'Send test email', 'ct-custom' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}
}
