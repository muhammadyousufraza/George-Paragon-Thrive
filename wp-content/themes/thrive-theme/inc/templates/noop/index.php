<?php
/**
 * Main template file for the "noop" theme (handles both list and singular)
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
get_header(); ?>
	<main id="wrapper" role="main">
		<div id="content">
			<div class="main-container thrv_wrapper">
				<div class="theme-section content-section" style="display: block">
					<?php
					get_template_part( 'inc/templates/noop/template-parts/header' );
					$archive_title    = '';
					$archive_subtitle = '';

					if ( is_search() ) {
						global $wp_query;

						$archive_title = sprintf(
							'%1$s %2$s',
							'<span class="color-accent">' . __( 'Search:', 'thrive-theme' ) . '</span>',
							'&ldquo;' . get_search_query() . '&rdquo;'
						);

						if ( $wp_query->found_posts ) {
							$archive_subtitle = sprintf(
							/* translators: %s: Number of search results */
								_n(
									'We found %s result for your search.',
									'We found %s results for your search.',
									$wp_query->found_posts,
									'thrive-theme'
								),
								number_format_i18n( $wp_query->found_posts )
							);
						} else {
							$archive_subtitle = __( 'We could not find any results for your search. You can give it another try through the search form below.', 'thrive-theme' );
						}
					} elseif ( ! is_home() && ! is_singular() ) {
						$archive_title    = get_the_archive_title();
						$archive_subtitle = get_the_archive_description();
					}

					if ( $archive_title || $archive_subtitle ) {
						?>

						<header class="archive-header has-text-align-center header-footer-group">

							<div class="archive-header-inner section-inner medium">

								<?php if ( $archive_title ) { ?>
									<h1 class="archive-title"><?php echo wp_kses_post( $archive_title ); ?></h1>
								<?php } ?>

								<?php if ( $archive_subtitle ) { ?>
									<div class="archive-subtitle section-inner thin max-percentage intro-text"><?php echo wp_kses_post( wpautop( $archive_subtitle ) ); ?></div>
								<?php } ?>

							</div><!-- .archive-header-inner -->

						</header><!-- .archive-header -->

						<?php
					}

					if ( have_posts() ) {

						$i = 0;

						while ( have_posts() ) {
							$i ++;
							if ( $i > 1 ) {
								echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
							}
							the_post();

							get_template_part( 'inc/templates/noop/template-parts/content', get_post_type() );

						}
					} elseif ( is_search() ) {
						?>

						<div class="no-search-results-form section-inner thin">

							<?php
							get_search_form(
								array(
									'label' => __( 'search again', 'thrive-theme' ),
								)
							);
							?>

						</div><!-- .no-search-results -->

						<?php
					}
					?>

					<?php get_template_part( 'template-parts/pagination' ); ?>
				</div>
			</div>
		</div>
	</main><!-- #site-content -->
	<footer style="margin: 0 auto; max-width: 1080px; text-align: center; padding: 1rem 0 2rem">
		Copyright &copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>
	</footer>
<?php
get_footer();

