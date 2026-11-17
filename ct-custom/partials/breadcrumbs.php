<?php
/**
 * Breadcrumb trail with schema.org BreadcrumbList microdata.
 *
 * @package CtCustom
 */

use CtCustom\Breadcrumbs;

$ct_custom_trail = Breadcrumbs::trail( get_post() );
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ct-custom' ); ?>">
	<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
		<?php foreach ( $ct_custom_trail as $ct_custom_index => $ct_custom_crumb ) : ?>
			<li class="breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<?php if ( '' !== $ct_custom_crumb['url'] ) : ?>
					<a class="breadcrumbs__link" href="<?php echo esc_url( $ct_custom_crumb['url'] ); ?>" itemprop="item"><span itemprop="name"><?php echo esc_html( $ct_custom_crumb['label'] ); ?></span></a>
				<?php else : ?>
					<span class="breadcrumbs__current" itemprop="name" aria-current="page"><?php echo esc_html( $ct_custom_crumb['label'] ); ?></span>
				<?php endif; ?>
				<meta itemprop="position" content="<?php echo esc_attr( (string) ( $ct_custom_index + 1 ) ); ?>">
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
