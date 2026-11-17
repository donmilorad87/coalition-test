<?php
/**
 * Header bar: logo + primary navigation.
 *
 * @package CtCustom
 */

use CtCustom\Icons;
use CtCustom\Menu;
use CtCustom\Settings;

$ct_custom_logo_id = (int) Settings::get( 'logo_id' );
?>
<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( $ct_custom_logo_id ) : ?>
				<?php
				echo wp_get_attachment_image(
					$ct_custom_logo_id,
					'full',
					false,
					array(
						'class'         => 'site-logo__img',
						'alt'           => get_bloginfo( 'name' ),
						'loading'       => 'eager',
						'fetchpriority' => 'high',
					)
				);
				?>
			<?php else : ?>
				<span class="site-logo__text"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="main-nav" data-main-nav aria-label="<?php esc_attr_e( 'Primary', 'ct-custom' ); ?>">
			<button type="button" class="main-nav__burger" aria-expanded="false" aria-controls="primary-menu">
				<?php echo Icons::get( 'menu' ) . Icons::get( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'ct-custom' ); ?></span>
			</button>
			<?php Menu::render(); ?>
		</nav>
	</div>
</header>
