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

<div id="tve-shop-template-component" class="tve-component" data-view="ShopOptions">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Product List Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="center-xs col-xs-12 mb-10">
			<button class="tve-button orange click" data-fn="editProducts">
				<?php echo __( 'Edit Design', 'thrive-theme' ); ?>
			</button>
		</div>
		<div class="hide-tablet hide-mobile">
			<hr>
			<div class="tve-control" data-view="PostsPerPage"></div>
			<div class="tve-control" data-view="Columns"></div>
			<div class="tve-control" data-view="OrderBy"></div>
			<div class="tve-control" data-view="Order"></div>
			<hr>
			<div class="tve-control" data-view="result-count-visibility"></div>
			<div class="tve-control" data-view="catalog-ordering-visibility"></div>
			<div class="tve-control" data-view="sale-flash-visibility"></div>
			<div class="tve-control" data-view="title-visibility"></div>
			<div class="tve-control" data-view="rating-visibility"></div>
			<div class="tve-control" data-view="price-visibility"></div>
			<div class="tve-control" data-view="cart-visibility"></div>
			<div class="tve-control" data-view="pagination-visibility"></div>
			<hr>
			<div class="tve-control" data-view="Alignment"></div>
			<div class="tve-control" data-view="ImageSize"></div>
		</div>
	</div>
</div>
