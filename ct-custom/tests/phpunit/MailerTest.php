<?php
/**
 * SMTP settings (Theme Settings → Email) applied to PHPMailer.
 *
 * @package CtCustom
 */

use CtCustom\Mailer;
use CtCustom\Settings;

/**
 * @covers \CtCustom\Mailer
 * @covers \CtCustom\Settings
 */
class MailerTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( Settings::OPTION );
		parent::tear_down();
	}

	/**
	 * Fresh PHPMailer instance.
	 */
	private function mailer(): PHPMailer\PHPMailer\PHPMailer {
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
		return new PHPMailer\PHPMailer\PHPMailer( true );
	}

	public function test_sanitize_mail_fields(): void {
		$clean = Settings::sanitize(
			array(
				'mail_enabled'    => '1',
				'mail_host'       => 'smtp.example.com<script>',
				'mail_port'       => '99999',
				'mail_encryption' => 'rot13',
				'mail_username'   => ' user ',
				'mail_password'   => 'p@ss w<rd>',
				'mail_from_email' => 'not-an-email',
				'mail_from_name'  => '<b>Site</b>',
			)
		);
		$this->assertTrue( $clean['mail_enabled'] );
		$this->assertSame( 'smtp.example.com', $clean['mail_host'] );
		$this->assertSame( 587, $clean['mail_port'] );
		$this->assertSame( '', $clean['mail_encryption'] );
		$this->assertSame( 'user', $clean['mail_username'] );
		$this->assertSame( 'p@ss w<rd>', $clean['mail_password'], 'Passwords are stored verbatim (only trimmed).' );
		$this->assertSame( '', $clean['mail_from_email'] );
		$this->assertSame( 'Site', $clean['mail_from_name'] );
	}

	public function test_blank_password_keeps_the_saved_one(): void {
		update_option( Settings::OPTION, array( 'mail_password' => 'secret' ) );
		$this->assertSame( 'secret', Settings::sanitize( array( 'mail_password' => '' ) )['mail_password'] );
	}

	public function test_configure_switches_phpmailer_to_smtp_when_enabled(): void {
		update_option(
			Settings::OPTION,
			array(
				'mail_enabled'    => true,
				'mail_host'       => 'sandbox.smtp.mailtrap.io',
				'mail_port'       => 2525,
				'mail_encryption' => 'tls',
				'mail_username'   => 'user',
				'mail_password'   => 'secret',
			)
		);
		$mailer = $this->mailer();
		Mailer::configure( $mailer );

		$this->assertSame( 'smtp', $mailer->Mailer );
		$this->assertSame( 'sandbox.smtp.mailtrap.io', $mailer->Host );
		$this->assertSame( 2525, $mailer->Port );
		$this->assertSame( 'tls', $mailer->SMTPSecure );
		$this->assertTrue( $mailer->SMTPAuth );
		$this->assertSame( 'user', $mailer->Username );
		$this->assertSame( 'secret', $mailer->Password );
	}

	public function test_configure_leaves_phpmailer_alone_when_disabled(): void {
		update_option( Settings::OPTION, array( 'mail_enabled' => false, 'mail_host' => 'smtp.example.com' ) );
		$mailer = $this->mailer();
		Mailer::configure( $mailer );
		$this->assertSame( 'mail', $mailer->Mailer );
	}

	public function test_from_address_filters(): void {
		update_option( Settings::OPTION, array( 'mail_enabled' => true, 'mail_host' => 'h', 'mail_from_email' => 'hi@example.com', 'mail_from_name' => 'Coalition' ) );
		$this->assertSame( 'hi@example.com', Mailer::from_email( 'wordpress@localhost' ) );
		$this->assertSame( 'Coalition', Mailer::from_name( 'WordPress' ) );

		update_option( Settings::OPTION, array( 'mail_enabled' => true, 'mail_host' => 'h' ) );
		$this->assertSame( 'wordpress@localhost', Mailer::from_email( 'wordpress@localhost' ) );
	}
}
