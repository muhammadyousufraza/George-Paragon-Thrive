<?php
/**
 * Displays a navigation section on singular pages with Next / Prev links for the "noop" theme
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$next_post = get_next_post();
$prev_post = get_previous_post();

if ( $next_post || $prev_post ) {

	$pagination_classes = '';

	if ( ! $next_post ) {
		$pagination_classes = ' only-one only-prev';
	} elseif ( ! $prev_post ) {
		$pagination_classes = ' only-one only-next';
	}

	?>
	<hr style="margin: 10px 0;"/>
	<nav role="navigation" style="display: flex; justify-content: space-between">
		<?php
		if ( $prev_post ) {
			?>

			<a class="previous-post" href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>">
				<span class="arrow" aria-hidden="true">&larr;</span>
				<span class="title"><span class="title-inner"><?php echo wp_kses_post( get_the_title( $prev_post->ID ) ); ?></span></span>
			</a>

			<?php
		}

		if ( $next_post ) {
			?>

			<a class="next-post" href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>">
				<span class="title"><span class="title-inner"><?php echo wp_kses_post( get_the_title( $next_post->ID ) ); ?></span></span>
				<span class="arrow" aria-hidden="true">&rarr;</span>
			</a>
			<?php
		}
		?>
	</nav><!-- .pagination-single -->
	<hr style="margin: 10px 0;"/>

	<?php
}
