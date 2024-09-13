<?php
/**
 * Content template for the "noop" theme
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<?php

	if ( is_singular() ) {
		the_title( '<h1 class="entry-title">', '</h1>' );
	} else {
		the_title( '<h2 class="entry-title heading-size-1"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
	}

	if ( ! is_search() ) {
		get_template_part( 'inc/templates/noop/template-parts/featured-image' );
	}

	?>

	<div class="entry-content">

		<?php
		if ( is_search() || ! is_singular() && 'summary' === get_theme_mod( 'blog_content', 'full' ) ) {
			the_excerpt();
		} else {
			the_content( __( 'Continue reading', 'thrive-theme' ) );
		}
		?>

	</div><!-- .entry-content -->

	<div class="section-inner">
		<?php
		wp_link_pages(
			array(
				'before'      => '<nav class="post-nav-links bg-light-background" aria-label="' . esc_attr__( 'Page', 'thrive-theme' ) . '"><span class="label">' . __( 'Pages:', 'thrive-theme' ) . '</span>',
				'after'       => '</nav>',
				'link_before' => '<span class="page-number">',
				'link_after'  => '</span>',
			)
		);

		edit_post_link();

		if ( is_single() ) {

			get_template_part( 'template-parts/entry-author-bio' );

		}
		?>

	</div><!-- .section-inner -->

	<?php

	if ( is_single() ) {

		get_template_part( 'inc/templates/noop/template-parts/navigation' );

	}
	?>

</article><!-- .post -->
