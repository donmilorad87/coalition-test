<?php
/**
 * Reach Us block markup. Empty attributes fall back to Theme Settings.
 *
 * @package CtCustom
 *
 * @var array $attributes Block attributes.
 */

use CtCustom\Icons;
use CtCustom\Settings;

defined( 'ABSPATH' ) || exit;

$ct_custom_value = static function ( string $key ) use ( $attributes ): string {
	$override = trim( (string) ( $attributes[ $key ] ?? '' ) );
	return '' !== $override ? $override : (string) Settings::get( $key );
};

$ct_custom_company   = $ct_custom_value( 'company' );
$ct_custom_address   = $ct_custom_value( 'address' );
$ct_custom_show_copy = ! empty( $attributes['showCopy'] );
$ct_custom_lines     = array(
	array( __( 'Phone:', 'ct-custom' ), Settings::sanitize_phone( $ct_custom_value( 'phone' ) ) ),
	array( __( 'Fax:', 'ct-custom' ), Settings::sanitize_phone( $ct_custom_value( 'fax' ) ) ),
);
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'reach-us' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="ct-custom/reach-us"
	<?php echo wp_interactivity_data_wp_context( array( 'message' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<?php if ( '' !== $ct_custom_company ) : ?>
		<p class="reach-us__company"><?php echo esc_html( $ct_custom_company ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $ct_custom_address ) : ?>
		<p class="reach-us__address"><?php echo nl2br( esc_html( $ct_custom_address ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped before nl2br. ?></p>
	<?php endif; ?>

	<p class="reach-us__numbers">
		<?php foreach ( $ct_custom_lines as list( $ct_custom_label, $ct_custom_number ) ) : ?>
			<?php
			if ( '' === $ct_custom_number ) {
				continue;
			}
			?>
			<span class="reach-us__line">
				<?php echo esc_html( $ct_custom_label ); ?> <a href="<?php echo esc_url( Settings::tel_href( $ct_custom_number ), array( 'tel' ) ); ?>" class="reach-us__number"><?php echo esc_html( $ct_custom_number ); ?></a>
				<?php if ( $ct_custom_show_copy ) : ?>
					<button
						type="button"
						class="reach-us__copy"
						hidden
						data-value="<?php echo esc_attr( $ct_custom_number ); ?>"
						data-wp-init="callbacks.reveal"
						data-wp-on--click="actions.copy"
					>
						<?php echo Icons::get( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: label such as "Phone:". */
							echo esc_html( sprintf( __( 'Copy %s', 'ct-custom' ), rtrim( $ct_custom_label, ':' ) ) );
							?>
						</span>
					</button>
				<?php endif; ?>
			</span>
		<?php endforeach; ?>
	</p>

	<?php if ( $ct_custom_show_copy ) : ?>
		<p
			class="reach-us__feedback"
			role="status"
			data-copied="<?php esc_attr_e( 'Copied to clipboard', 'ct-custom' ); ?>"
			data-failed="<?php esc_attr_e( 'Copy failed — please copy manually', 'ct-custom' ); ?>"
			data-wp-text="context.message"
		></p>
	<?php endif; ?>
</div>
