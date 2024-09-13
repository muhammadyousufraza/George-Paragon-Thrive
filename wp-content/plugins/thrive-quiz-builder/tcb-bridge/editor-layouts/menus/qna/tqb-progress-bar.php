<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-tqb_progress_bar-component" class="tve-component" data-view="progressBar">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-quiz-builder' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control gl-st-button-toggle-1 hide-states" data-view="Palettes"></div>
		<div class="tve-control" data-view="ProgressBarPosition"></div>
		<div class="tve-control" data-view="ProgressBarType"></div>
		<div class="tve-progress-bar-label">
				<span>
					<?php echo esc_html__( 'Progress bar Label', 'thrive-quiz-builder' ); ?>
				</span>
			<div class="mt-10">
				<input type="text" class="change" data-fn="changePgLabel" value=""/>
			</div>
		</div>
	</div>
</div>
