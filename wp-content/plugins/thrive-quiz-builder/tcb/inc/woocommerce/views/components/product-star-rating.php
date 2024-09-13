<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-product-star-rating-component" class="tve-component" data-view="ProductStarRating">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Star Rating', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="color"></div>
		<div class="tve-control" data-view="size"></div>
	</div>
</div>
