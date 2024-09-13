<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<span class="tcb-modal-title mt-0 ml-0"><?php echo esc_html__( 'Confirmation', 'thrive-quiz-builder' ); ?></span>
<div class="margin-top-20">
	<?php echo esc_html__( 'Are you sure you want to equalize all intervals?', 'thrive-quiz-builder' ) ?>
</div>
<div class="tcb-modal-footer flex-end pr-0">
	<button type="button" class="tcb-right tve-button medium white-text red click" data-fn="equalize_intervals">
		<?php echo esc_html__( 'Equalize Sizes', 'thrive-quiz-builder' ) ?>
	</button>
</div>
