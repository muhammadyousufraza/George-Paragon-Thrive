<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/25/2017
 * Time: 2:28 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-quiz-component" class="tve-component" data-view="quiz">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Quiz Options', 'thrive-quiz-builder' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="change_quiz"></div>
		<div class="tve-control" data-view="quiz_scroll"></div>
		<div class="tve-control" data-view="SaveUserQuizProgress"></div>
		<div class="tve-advanced-controls extend-grey tqb-redirection-override">
			<div class="dropdown-header" data-prop="advanced">
				<span><?php esc_html_e( 'Redirection override', 'thrive-quiz-builder' ); ?></span>
			</div>
			<div class="dropdown-content pt-0">
				<div>
					<span><?php esc_html_e( 'Automatic redirection is disabled when saving the users quiz progress. Users will be shown a link to their results', 'thrive-quiz-builder' ); ?></span>
				</div>
				<hr class="mt-5 mb-5">
				<div class="tqb-redirection-override-extra-controls"></div>
			</div>
		</div>
		<?php
		/**
		 * Allows other plugins to insert additional controls to the quiz component
		 */
		do_action( 'tcb_tqb_quiz_component_menu_after_controls' );
		?>
	</div>
</div>
