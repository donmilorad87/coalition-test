<?php
/**
 * Shared page layout: breadcrumbs, heading and block content. Used by homepage.php and page.php.
 *
 * @package CtCustom
 */

use CtCustom\Breadcrumbs;

?>
<main id="main" class="site-main page-main">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>
				<header class="page-header">
					<?php get_template_part( 'partials/breadcrumbs' ); ?>
					<h1 class="page-title"><?php echo esc_html( Breadcrumbs::page_title( get_post() ) ); ?></h1>
				</header>

				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ct-custom' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>
