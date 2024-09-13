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
[tva_assessment_type type="<?php echo \TVA\Architect\Assessment\Main::TYPE_UPLOAD; ?>"]
<div class="tve-content-box-background"></div>
<div class="tve-cb">
	<div class="thrv_wrapper thrv_text_element">
		<div class="tcb-plain-text">Submit your assessment here</div>
	</div>
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/submit-file.php' ); ?>
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/submit-button.php' ); ?>
</div>
[/tva_assessment_type]
[tva_assessment_type type="<?php echo \TVA\Architect\Assessment\Main::TYPE_QUIZ; ?>"]
<div class="tve-content-box-background"></div>
<div class="tve-cb">
	[tva_tqb_assessment_quiz]
</div>
[/tva_assessment_type]
[tva_assessment_type type="<?php echo \TVA\Architect\Assessment\Main::TYPE_YOUTUBE_LINK; ?>"]
<div class="tve-content-box-background"></div>
<div class="tve-cb">
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/input.php' ); ?>
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/submit-button.php' ); ?>
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/video-preview.php' ); ?>
</div>
[/tva_assessment_type]
[tva_assessment_type type="<?php echo \TVA\Architect\Assessment\Main::TYPE_EXTERNAL_LINK; ?>"]
<div class="tve-content-box-background"></div>
<div class="tve-cb">
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/input.php' ); ?>
	<?php include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/assessment/submit-button.php' ); ?>
</div>
[/tva_assessment_type]
[tva_assessment_type type="<?php echo \TVA\Architect\Assessment\Main::TYPE_CONFIRMATION; ?>"]
<div class="tve-content-box-background"></div>
<div class="tve-cb">
	<div class="thrv_wrapper thrv_text_element">
		<div class="tcb-plain-text">Congratz!</div>
	</div>
</div>
[/tva_assessment_type]
[tva_assessment_type type="<?php echo \TVA\Architect\Assessment\Main::TYPE_RESULTS; ?>"]
<div class="tve-content-box-background"></div>
<div class="tve-cb">
	<div class="thrv_wrapper thrv_text_element">
		<div class="tcb-plain-text">Results!</div>
	</div>
	[tva_assessment_result_list]
</div>
[/tva_assessment_type]
