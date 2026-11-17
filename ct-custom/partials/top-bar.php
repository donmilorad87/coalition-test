<?php
/**
 * Top bar: call-to-action phone number and account links.
 *
 * @package CtCustom
 */

use CtCustom\Customizer;
use CtCustom\Settings;

$ct_custom_phone = (string) Settings::get( 'phone' );
?>
<div class="top-bar">
	<div class="container top-bar__inner">
		<p class="top-bar__call">
			<span class="top-bar__label"><?php echo esc_html( (string) Customizer::option( 'ct_topbar_label' ) ); ?></span>
			<?php if ( '' !== $ct_custom_phone ) : ?>
				<a class="top-bar__phone" href="<?php echo esc_url( Settings::tel_href( $ct_custom_phone ), array( 'tel' ) ); ?>"><?php echo esc_html( $ct_custom_phone ); ?></a>
			<?php endif; ?>
		</p>

		<nav class="top-bar__account" aria-label="<?php esc_attr_e( 'Account', 'ct-custom' ); ?>">
			<?php if ( is_user_logged_in() ) : ?>
				<a class="top-bar__label" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>"><?php esc_html_e( 'Account', 'ct-custom' ); ?></a>
				<a class="top-bar__highlight" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Logout', 'ct-custom' ); ?></a>
			<?php else : ?>
				<a class="top-bar__label" href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Login', 'ct-custom' ); ?></a>
				<?php if ( get_option( 'users_can_register' ) ) : ?>
					<a class="top-bar__highlight" href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Signup', 'ct-custom' ); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</nav>
	</div>
</div>
