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
<div id="tve-megamenu_item-component" class="tve-component" data-view="MegamenuItem">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="HasIconImage"></div>
		<div class="tve-control pb-10 i-enabled gl-st-icon-toggle-2" data-view="ModalPicker"></div>
		<div class="if-image tve-control mb-10" data-view="ImageSide"></div>
		<div class="i-selected i-enabled pb-10">
			<div class="tve-control no-space gl-st-icon-toggle-1" data-view="ColorPicker"></div>
			<div class="hide-states pt-10">
				<div class="tve-control gl-st-icon-toggle-1" data-view="Slider"></div>
			</div>
		</div>
		<div class="tve-control link-control hide-states" data-key="link" data-initializer="elementLink"></div>
	</div>
</div>
