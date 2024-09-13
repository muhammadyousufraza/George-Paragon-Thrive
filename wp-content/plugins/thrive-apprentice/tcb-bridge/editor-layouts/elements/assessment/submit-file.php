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
<form action="#" method="post" novalidate="novalidate">
	<div class="thrv_wrapper tve_lg_file thrv-content-box tcb-no-clone tcb-no-delete tcb-no-save" draggable="true" data-f-id="tva-assessments" data-file-setup="[tva_assessment_upload_config][/tva_assessment_upload_config]">
		<div class="tve-content-box-background"></div>
		<div class="tve-cb">
			<input data-mapping="tva-assessment-file" type="file" name="file">
			<div data-style-d="circle_inverted" class="thrv_wrapper tcb-default-upload-icon thrv_icon tve-draggable tve-droppable tcb-selector-no_save tcb-icon-display tcb-local-vars-root">
				<svg class="tcb-icon" viewBox="0 0 24 24" data-id="icon-file-upload-solid" data-name="">
					<path d="M14,2L20,8V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V4A2,2 0 0,1 6,2H14M18,20V9H13V4H6V20H18M12,12L16,16H13.5V19H10.5V16H8L12,12Z"></path>
				</svg>
			</div>
			<div class="thrv_wrapper thrv_text_element tcb-default-upload-text">
				<div class="tcb-plain-text" style="text-align: center;">Drag and drop files here<br>or</div>
			</div>
			<div class="thrv_wrapper tcb-file-upload-btn thrv-button thrv-button-v2 tcb-local-vars-root tcb-selector-no_delete tcb-selector-no_save tcb-selector-no_clone">
				<a href="javascript:void(0)" class="tcb-button-link tcb-plain-text tcb-file-upload-trigger">
					<span class="tcb-button-texts"><span class="tcb-button-text thrv-inline-text">Select files</span></span>
				</a>
			</div>
		</div>
	</div>
</form>
