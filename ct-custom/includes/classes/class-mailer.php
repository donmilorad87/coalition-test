<?php
/**
 * Outgoing email through the SMTP server configured in Theme Settings → Email.
 *
 * @package CtCustom
 */

namespace CtCustom;

use PHPMailer\PHPMailer\PHPMailer;

defined( 'ABSPATH' ) || exit;

/**
 * Configures wp_mail() (used by Contact Form 7) and offers a "send test email" action.
 */
final class Mailer {

	public const TEST_ACTION = 'ct_custom_test_mail';

	/**
	 * Register hooks.
	 */
	public static function start(): void {
		add_action( 'phpmailer_init', array( self::class, 'configure' ) );
		add_filter( 'wp_mail_from', array( self::class, 'from_email' ) );
		add_filter( 'wp_mail_from_name', array( self::class, 'from_name' ) );
		add_action( 'admin_post_' . self::TEST_ACTION, array( self::class, 'send_test' ) );
		add_action( 'wp_mail_failed', array( self::class, 'log_failure' ) );
	}

	/**
	 * SMTP is used only when enabled and a host is set.
	 */
	public static function is_enabled(): bool {
		return (bool) Settings::get( 'mail_enabled' ) && '' !== (string) Settings::get( 'mail_host' );
	}

	/**
	 * `phpmailer_init`: switch PHPMailer to SMTP.
	 *
	 * @param PHPMailer $phpmailer Mailer instance (passed by reference by core).
	 */
	public static function configure( $phpmailer ): void {
		if ( ! self::is_enabled() ) {
			return;
		}
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$phpmailer->isSMTP();
		$phpmailer->Host        = (string) Settings::get( 'mail_host' );
		$phpmailer->Port        = (int) Settings::get( 'mail_port' );
		$phpmailer->SMTPSecure  = (string) Settings::get( 'mail_encryption' );
		$phpmailer->SMTPAutoTLS = '' !== $phpmailer->SMTPSecure;
		$phpmailer->Username    = (string) Settings::get( 'mail_username' );
		$phpmailer->Password    = (string) Settings::get( 'mail_password' );
		$phpmailer->SMTPAuth    = '' !== $phpmailer->Username;
		// phpcs:enable
	}

	/**
	 * `wp_mail_from`.
	 *
	 * @param string $email Default sender.
	 */
	public static function from_email( string $email ): string {
		$from = (string) Settings::get( 'mail_from_email' );
		return ( self::is_enabled() && '' !== $from ) ? $from : $email;
	}

	/**
	 * `wp_mail_from_name`.
	 *
	 * @param string $name Default sender name.
	 */
	public static function from_name( string $name ): string {
		$from = (string) Settings::get( 'mail_from_name' );
		return ( self::is_enabled() && '' !== $from ) ? $from : $name;
	}

	/**
	 * Log delivery errors (CF7 only shows a generic message to visitors).
	 *
	 * @param \WP_Error $error Mail error.
	 */
	public static function log_failure( $error ): void {
		error_log( 'ct-custom: wp_mail failed: ' . $error->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Handle admin-post: send a test email to the current user and report the result.
	 */
	public static function send_test(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'ct-custom' ), 403 );
		}
		check_admin_referer( self::TEST_ACTION );

		// Single admin-triggered test message, not bulk mail.
		$sent = wp_mail( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
			wp_get_current_user()->user_email,
			__( 'Test email from your theme settings', 'ct-custom' ),
			__( 'SMTP is configured correctly: this message was sent through the server set in Appearance → Theme Settings → Email.', 'ct-custom' )
		);

		wp_safe_redirect(
			add_query_arg( 'ct-mail-test', $sent ? 'sent' : 'failed', admin_url( 'themes.php?page=' . Settings_Page::SLUG ) )
		);
		exit;
	}
}
