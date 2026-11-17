<?php
/**
 * Social Links block markup. Items (image, name, URL) are rendered in order;
 * with no valid items the Theme Settings links are used.
 *
 * @package CtCustom
 *
 * @var array $attributes Block attributes.
 */

use CtCustom\Icons;
use CtCustom\Settings;

defined( 'ABSPATH' ) || exit;

// Block items first; without any valid item, fall back to Appearance → Theme Settings.
$ct_custom_items = Settings::sanitize_social_items( $attributes['items'] ?? array() );
if ( ! $ct_custom_items ) {
	$ct_custom_items = Settings::socials();
}

$ct_custom_show_share = ! empty( $attributes['showShare'] );
if ( ! $ct_custom_items && ! $ct_custom_show_share ) {
	return;
}

$ct_custom_target = empty( $attributes['openInNewTab'] ) ? '' : ' target="_blank" rel="noopener noreferrer"';
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'social-links' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="ct-custom/social-links"
	<?php echo wp_interactivity_data_wp_context( array( 'message' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<ul class="social-links__list">
		<?php foreach ( $ct_custom_items as $ct_custom_item ) : ?>
			<?php $ct_custom_key = sanitize_key( $ct_custom_item['name'] ); ?>
			<li>
				<a class="social-links__link social-links__link--<?php echo esc_attr( $ct_custom_key ); ?>" href="<?php echo esc_url( $ct_custom_item['url'] ); ?>"<?php echo $ct_custom_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static string. ?>>
					<?php
					if ( $ct_custom_item['image_id'] ) {
						echo wp_get_attachment_image(
							$ct_custom_item['image_id'],
							'thumbnail',
							false,
							array(
								'class'   => 'social-links__img',
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
					} elseif ( Icons::has( $ct_custom_key ) ) {
						echo Icons::get( $ct_custom_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
					} else {
						printf( '<span class="social-links__initial" aria-hidden="true">%s</span>', esc_html( mb_strtoupper( mb_substr( $ct_custom_item['name'], 0, 1 ) ) ) );
					}
					?>
					<span class="screen-reader-text"><?php echo esc_html( $ct_custom_item['name'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>

		<?php if ( $ct_custom_show_share ) : ?>
			<li>
				<button
					type="button"
					class="social-links__link social-links__share"
					hidden
					data-copied="<?php esc_attr_e( 'Page link copied', 'ct-custom' ); ?>"
					data-failed="<?php esc_attr_e( 'Sharing failed', 'ct-custom' ); ?>"
					data-wp-init="callbacks.reveal"
					data-wp-on--click="actions.share"
				>
					<?php echo Icons::get( 'share' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Share this page', 'ct-custom' ); ?></span>
				</button>
			</li>
		<?php endif; ?>
	</ul>
	<?php if ( $ct_custom_show_share ) : ?>
		<p class="social-links__feedback" role="status" data-wp-text="context.message"></p>
	<?php endif; ?>
</div>
