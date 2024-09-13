<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<div id="tve-video_cover-component" class="tve-component" data-view="VideoCover">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="VideoCoverPalette"></div>
		<div class="tve-control mb-5" data-view="ThumbnailType"></div>
		<div class="tve-video-thumbnail-control"></div>
		<div class="tve-control pt-5" data-view="PreloadThumbnail"></div>
		<hr class="mt-5 mb-5">
		<div class="tve-control" data-view="ImageOverlaySwitch"></div>
		<div class="tve-control" data-view="ImageOverlay"></div>
		<hr class="mt-5 mb-5">
		<div class="tve-control mb-5" data-view="VerticalPosition"></div>
	</div>
</div>
