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

<div id="tve-product-gallery-component" class="tve-component" data-view="ProductGallery">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Product Gallery', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="DisplayMagnifier"></div>
		<div class="tve-control" data-view="GalleryWidth"></div>
		<div class="tve-control" data-view="Columns"></div>
	</div>
</div>
