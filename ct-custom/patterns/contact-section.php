<?php
/**
 * Title: Contact section
 * Slug: ct-custom/contact-section
 * Categories: ct-custom
 * Description: Two columns from the design: "Contact us" (Contact Form 7) and "Reach us" (Reach Us + Social Links blocks).
 * Keywords: contact, form, reach us, address, social
 *
 * @package CtCustom
 */

?>
<!-- wp:columns {"className":"ct-contact-columns"} -->
<div class="wp-block-columns ct-contact-columns"><!-- wp:column {"width":"48.96%"} -->
<div class="wp-block-column" style="flex-basis:48.96%"><!-- wp:heading {"className":"is-style-ct-section"} -->
<h2 class="wp-block-heading is-style-ct-section"><?php esc_html_e( 'Contact us', 'ct-custom' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[contact-form-7 title="Contact us"]
<!-- /wp:shortcode --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"43.02%"} -->
<div class="wp-block-column" style="flex-basis:43.02%"><!-- wp:heading {"className":"is-style-ct-section"} -->
<h2 class="wp-block-heading is-style-ct-section"><?php esc_html_e( 'Reach us', 'ct-custom' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:ct-custom/reach-us /-->

<!-- wp:ct-custom/social-links {"style":{"spacing":{"margin":{"top":"61px"}}}} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
