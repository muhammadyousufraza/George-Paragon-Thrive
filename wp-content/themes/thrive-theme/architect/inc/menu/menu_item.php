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
<div id="tve-menu_item-component" class="tve-component" data-view="MenuItem">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control hide-states" data-view="Display"></div>
		<div class="tve-control hide-tablet hide-mobile hide-states" data-view="HoverEffect"></div>
		<div class="tve-control hide-hamburger" data-view="StyleChange"></div>
		<div class="tve-control hide-hamburger" data-view="StylePicker" data-initializer="style"></div>
		<div class="hide-states">
			<div class="tve-control" data-view="HasIconImage"></div>
			<div class="tve-control i-enabled gl-st-icon-toggle-2" data-view="ModalPicker"></div>
			<div class="if-image tve-control mb-10" data-view="ImageSide"></div>
		</div>
		<div class="i-selected i-enabled">
			<div class="tve-control no-space gl-st-icon-toggle-1 mb-10" data-view="ColorPicker"></div>
			<div class="hide-states mb-10">
				<div class="tve-control gl-st-icon-toggle-1" data-view="Slider"></div>
			</div>
		</div>
		<hr>
		<div class="tve-control hide-tablet hide-mobile hide-states mt-10" data-key="link" data-initializer="elementLink"></div>
	</div>
</div>
