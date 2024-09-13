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

<div id="tve-product-template-component" class="tve-component" data-view="ProductOptions">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Product Display Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="center-xs col-xs-12 mb-10 edit-mode-hidden">
			<button class="tve-button orange click" data-fn="editTemplate">
				<?php echo __( 'Edit Design', 'thrive-theme' ); ?>
			</button>
		</div>
		<div class="hide-tablet hide-mobile">
			<hr class="edit-mode-hidden">
			<div class="tve-control" data-view="TitleVisibility"></div>
			<div class="tve-control" data-view="PriceVisibility"></div>
			<div class="tve-control" data-view="DescriptionVisibility"></div>
			<div class="tve-control" data-view="ButtonVisibility"></div>
			<div class="tve-control" data-view="MetaVisibility"></div>
			<div class="tve-control mb-5" data-view="ReviewVisibility"></div>
		</div>
	</div>
</div>

