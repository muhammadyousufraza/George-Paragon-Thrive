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
<div id="tve-assessment_result_list-component" class="tve-component" data-view="AssessmentResultList">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main options', 'thrive-apprentice' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center">
			<button class="tve-button orange mb-10 click" data-fn="editElement">
				<?php echo __( 'Edit Design', 'thrive-apprentice' ); ?>
			</button>
		</div>
	</div>
</div>
