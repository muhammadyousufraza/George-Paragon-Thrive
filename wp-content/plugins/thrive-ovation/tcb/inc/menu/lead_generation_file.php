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
<div id="tve-lead_generation_file-component" class="tve-component" data-view="LeadGenerationFile">
	<div class="dropdown-header" data-prop="docked">
		<?php esc_html_e( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="no-api tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="enterEditMode">
				<?php echo esc_html__( 'Edit File Upload', 'thrive-cb' ); ?>
			</button>
		</div>
		<hr class="mt-10 mb-10">
		<div class="tve-control" data-key="required" data-view="Checkbox"></div>
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control" data-key="file_types" data-initializer="getFileTypesControl"></div>
		<div class="tve-control" data-key="maxFiles" data-view="Input"></div>
		<div class="tve-control" data-key="maxSize" data-view="Input"></div>
	</div>
</div>
