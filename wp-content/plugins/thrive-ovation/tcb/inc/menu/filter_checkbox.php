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
<div id="tve-filter_checkbox-component" class="tve-component" data-view="FilterCheckbox">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="CheckboxPalettes"></div>
		<div class="tve-control tve-style-options no-space preview palettes-v2" data-view="StyleChange" data-has-palettes-v2="true"></div>
		<div class="tve-control" data-key="CheckboxStylePicker" data-initializer="checkboxStylePicker"></div>
		<div class="tve-control" data-view="CheckboxSize"></div>
	</div>
</div>