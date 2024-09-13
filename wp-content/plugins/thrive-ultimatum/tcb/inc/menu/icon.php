<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-icon-component" class="tve-component" data-view="Icon">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states pb-10">
			<div class="tve-control tve-choose-icon gl-st-icon-toggle-2" data-view="IconPicker"></div>
			<div class="tve-control" data-view="StylePicker" data-initializer="style"></div>
		</div>
		<div class="tve-control btn-group-light hide-states" data-view="ToggleColorControls"></div>
		<div class="tve-control no-space gl-st-icon-toggle-1 tcb-color-toggle-element tcb-icon-solid-color" data-view="ColorPicker"></div>
		<div class="tve-control tcb-color-toggle-element tcb-icon-gradient-color" data-view="GradientPicker"></div>
		<div class="tve-control tcb-color-toggle-element tcb-icon-gradient-color" data-view="StyleColor"></div>
		<div class="hide-states pt-10">
			<div class="tve-control gl-st-icon-toggle-1" data-view="Slider"></div>
			<div class="tve-control" data-view="RotateIcon"></div>
			<div class="tve-control" data-key="ToggleURL" data-extends="Switch" data-label="<?php echo esc_html__( 'Add link to icon', 'thrive-cb' ); ?>"></div>
			<div class="tve-control link-control" data-key="link" data-initializer="elementLink"></div>
		</div>
	</div>
</div>
