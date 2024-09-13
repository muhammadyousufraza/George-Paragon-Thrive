<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div id="tve-assessment_type_completed-component" class="tve-component" data-view="AssessmentConfirmation">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main options', 'thrive-apprentice' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mt-10" data-key="DisplayHistory"></div>
		<div class="tve-control mt-10" data-key="HideLatest"></div>
		<div class="tve-control mt-10" data-key="NumberOfItems"></div>
	</div>
</div>
