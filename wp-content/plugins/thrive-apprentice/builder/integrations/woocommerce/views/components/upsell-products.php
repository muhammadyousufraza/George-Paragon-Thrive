<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-upsells-products-component" class="tve-component" data-view="UpsellsProducts">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Upsell Products', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="Display"></div>
		<div class="upsells-products-controls">
			<div class="tve-control" data-view="Columns"></div>
			<div class="tve-control" data-view="PostsPerPage"></div>
			<div class="tve-control" data-view="OrderBy"></div>
			<div class="tve-control" data-view="Order"></div>
			<hr>
			<div class="tve-control" data-view="Alignment"></div>
			<div class="tve-control" data-view="ImageSize"></div>
		</div>
	</div>
</div>
