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
<div id="tve-lead_generation_textarea-component" class="tve-component" data-view="LeadGenerationTextarea">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control" data-key="placeholder" data-view="LabelInput"></div>
		<div class="tve-control" data-view="Rows"></div>
		<div class="tve-control" data-view="MinChar"></div>
		<div class="tve-control" data-view="MaxChar"></div>
		<div class="tve-control" data-view="ShowCounter"></div>
		<div class="tve-control" data-view="Resizing"></div>
	</div>
</div>
