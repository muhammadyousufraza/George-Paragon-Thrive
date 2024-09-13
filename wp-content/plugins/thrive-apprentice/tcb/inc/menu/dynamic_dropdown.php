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
<div id="tve-dynamic_dropdown-component" class="tve-component" data-view="DynamicDropdown">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tve-style-options no-space preview palettes-v2" data-view="StyleChange" data-has-palettes-v2="true"></div>
		<div class="tve-control" data-key="SelectStylePicker" data-initializer="stylePickerInitializer"></div>
		<hr>
		<div class="tve-control" data-view="Width"></div>
		<div class="tve-control" data-view="PlaceholderInput"></div>
		<div class="tve-control" data-key="DropdownIcon" data-initializer="dropdownIconInitializer"></div>
		<div class="tve-control mb-10" data-view="DropdownAnimation"></div>
	</div>
</div>
