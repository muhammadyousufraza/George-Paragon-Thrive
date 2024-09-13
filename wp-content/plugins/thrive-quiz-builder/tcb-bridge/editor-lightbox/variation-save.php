<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<span class="tcb-modal-title"><?php echo esc_html__( 'Save Page Template', 'thrive-quiz-builder' ); ?></span>
<div class="margin-top-20">
	<?php echo esc_html__( 'You can save the current page as a template for use on another post / page on your site.', 'thrive-quiz-builder' ) ?>
</div>
<div class="tve-templates-wrapper">
	<div class="tvd-input-field margin-bottom-5 margin-top-25">
		<input type="text" id="tve-template-name" required>
		<label for="tve-template-name"><?php echo esc_html__( 'Template Name', 'thrive-quiz-builder' ); ?></label>
	</div>
</div>
<div class="tcb-modal-footer flex-end">
	<button type="button" class="tcb-right tve-button medium green click white-text" data-fn="save">
		<?php echo esc_html__( 'Save Template', 'thrive-quiz-builder' ) ?>
	</button>
</div>
