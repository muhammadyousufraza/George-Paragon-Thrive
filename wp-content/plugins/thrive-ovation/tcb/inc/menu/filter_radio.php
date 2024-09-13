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
<div id="tve-filter_radio-component" class="tve-component" data-view="RadioFilter">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control gl-st-button-toggle-1" data-view="RadioPalettes"></div>
		<div class="tve-control tve-style-options no-space preview palettes-v2" data-view="StyleChange" data-has-palettes-v2="true"></div>
		<div class="tve-control" data-key="RadioStylePicker" data-initializer="radioStylePicker"></div>
		<hr>
		<div class="tve-control" data-view="RadioSize"></div>
	</div>
</div>
