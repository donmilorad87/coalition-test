<?php
/**
 * Site footer.
 *
 * @package CtCustom
 */

use CtCustom\Customizer;

?>
	</div><!-- #content -->

	<?php
	if ( Customizer::option( 'ct_show_footer' ) ) {
		get_template_part( 'partials/site-footer' );
	}
	?>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
