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
<div id="tve-tab_item-component" class="tve-component" data-view="TabItem">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="TextTypeDropdown"></div>
		<div class="tve-control" data-view="HasIcon"></div>
		<div class="tve-tab-icon-control">
			<div class="tve-control" data-view="ModalPicker"></div>
			<div class="tve-icon-prop-control pt-10">
				<div class="tve-control no-space" data-view="ColorPicker"></div>
				<div class="tve-control pt-10 pb-10" data-view="Slider"></div>
			</div>
		</div>
		<div class="tve-control" data-view="HasImage"></div>
		<div class="tve-tab-icon-position">
			<span class="label"><?php echo esc_html__( 'Icon / Image Position', 'thrive-cb' ); ?></span>
			<div class="tve-control mt-10" data-extends="ButtonGroup" data-view="IconPosition"></div>
		</div>
	</div>
</div>
