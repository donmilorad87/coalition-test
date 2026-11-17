<?php
/**
 * Footer bar: copyright + social links (reuses the Social Links block with Theme Settings values).
 *
 * @package CtCustom
 */

use CtCustom\Settings;

?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<p class="site-footer__copy">
			&copy; <?php echo esc_html( wp_date( 'Y' ) . ' ' . Settings::get( 'company' ) ); ?>
		</p>
		<?php echo do_blocks( '<!-- wp:ct-custom/social-links {"className":"site-footer__social"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output is escaped in render.php. ?>
	</div>
</footer>
