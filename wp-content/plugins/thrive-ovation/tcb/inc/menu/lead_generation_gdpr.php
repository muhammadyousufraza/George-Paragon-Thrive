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
<div id="tve-lead_generation_gdpr-component" class="tve-component" data-view="LeadGenerationGDPR">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control gl-st-button-toggle-1" data-view="CheckboxPalettes"></div>
		<div class="tve-control tve-style-options no-space preview" data-view="StyleChange"></div>
		<div class="tve-control" data-key="CheckboxStylePicker" data-initializer="checkboxStylePicker"></div>
		<div class="tve-control" data-view="CheckboxSize"></div>
	</div>
</div>
