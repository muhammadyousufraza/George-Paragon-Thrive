<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h1 class="page-title">
	<?php if ( have_posts() ) : ?>
		<?php printf( __( 'Search Results for: %s', 'thrive-theme' ), '<span>' . get_search_query() . '</span>' ); ?>
	<?php else : ?>
		<?php _e( 'Nothing Found', 'thrive-theme' ); ?>
	<?php endif; ?>
</h1>
