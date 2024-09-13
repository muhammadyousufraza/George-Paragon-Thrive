<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<span class="tcb-modal-title"><?php echo esc_html__( 'Confirmation', 'thrive-quiz-builder' ); ?></span>
<div class="margin-top-20">
	<?php echo esc_html__( 'By deleting the Dynamic Content Element from the page you\'ll loose all the settings you\'ve made to the intervals.', 'thrive-quiz-builder' ) ?>
</div>
<div class="tcb-modal-footer flex-end">
	<button type="button" class="tcb-right tve-button medium red click" data-fn="delete_all_dynamic_content">
		<?php echo esc_html__( 'Delete Dynamic Content', 'thrive-quiz-builder' ) ?>
	</button>
</div>
