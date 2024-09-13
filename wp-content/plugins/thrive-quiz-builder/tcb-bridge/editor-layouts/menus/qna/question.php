<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-tqb_question-component" class="tve-component" data-view="question">

	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-quiz-builder' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="no-api tcb-text-center login-elem-text mb-10 mr-5 ml-5">
				<button class="tve-button orange click" data-fn="resetQuestion">
					<?php echo esc_html__( 'Reset to default layout', 'thrive-quiz-builder' ); ?>
				</button>
			</div>
			<div class="tve-control gl-st-button-toggle-1 hide-states" data-view="Palettes"></div>
		</div>
	</div>

</div>
