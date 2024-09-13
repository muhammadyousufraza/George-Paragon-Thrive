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

<div id="tve-image_gallery-component" class="tve-component" data-view="ImageGallery">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-image-count">
			<span class="count-container">
				<span class="count"></span>
				<?php echo esc_html__( ' images active', 'thrive-cb' ); ?>
			</span>
			<button class="tve-ghost-green-button click" data-fn="placeholder_action"><?php echo esc_html__( 'Edit selection', 'thrive-cb' ); ?></button>
		</div>
		<div class="tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="enterEditMode">
				<?php echo esc_html__( 'Edit design', 'thrive-cb' ); ?>
			</button>
		</div>
		<hr>
		<div class="tve-control full-width" data-view="GalleryType"></div>
		<hr>
		<div class="tve-control" data-view="ShowCaptions"></div>
		<div class="tve-control" data-view="CropImages"></div>
		<div class="tve-crop-info info-text grey-text mt-0 mb-10"><?php echo esc_html__( 'Images can be repositioned by dragging them in edit mode', 'thrive-cb' ); ?></div>
		<div class="tve-control" data-view="GalleryImageHeight"></div>
		<div class="tve-control" data-view="Columns"></div>
		<div class="tve-control" data-view="HorizontalSpace"></div>
		<div class="tve-control" data-view="VerticalSpace"></div>
		<div class="tve-control" data-view="ColumnHeight"></div>
		<div class="tve-control" data-view="Gutter"></div>
		<hr>
		<div class="tve-control full-width" data-view="ThumbnailSize"></div>
		<div class="tve-control full-width" data-view="ClickBehavior"></div>
		<div class="tve-control" data-view="ShowCaptionsInLightbox"></div>
		<div class="tve-control full-width" data-view="FullscreenSize"></div>
		<div class="tve-advanced-controls tve-carousel-controls">
		</div>
	</div>
</div>
