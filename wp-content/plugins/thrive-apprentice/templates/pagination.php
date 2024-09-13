<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>

<div style="clear: both"></div>
<div class="tva-pagination-wrapper">
	<?php $args = array( 'query' => tva_get_courses_pagination_query( $arguments ) ); ?>
	<?php echo tva_get_paginated_numbers( $args, isset( $arguments['page'] ) ? $arguments['page'] : 1 ); ?>
</div>