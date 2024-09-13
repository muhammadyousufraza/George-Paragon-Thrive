<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<span class="tcb-modal-title"><?php echo esc_html__( 'Reset to default content', 'thrive-quiz-builder' ) ?></span>
<div class="margin-top-20">
	<?php echo esc_html__( 'Are you sure you want to reset this variation to the default template? This action cannot be undone.', 'thrive-quiz-builder' ) ?>
</div>
<div class="tcb-modal-footer flex-end">
	<button type="button" class="tcb-right tve-button medium red click" data-fn="reset">
		<?php echo esc_html__( 'Reset to default content', 'thrive-quiz-builder' ) ?>
	</button>
</div>
