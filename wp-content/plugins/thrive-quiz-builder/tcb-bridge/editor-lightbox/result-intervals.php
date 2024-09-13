<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$variation       = tqb_get_variation( isset( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ? absint( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) : '' );
$absolute_limits = tqb_compute_quiz_absolute_max_min_values( $variation['quiz_id'], true );
?>

<?php if ( false === $absolute_limits['min'] && false === $absolute_limits['max'] ) : // No questions defined ?>
	<?php $quiz_post = get_post( $variation['quiz_id'] ); ?>
	<span class="tcb-modal-title ml-0 mt-0"><?php echo esc_html__( 'You have no questions defined', 'thrive-quiz-builder' ); ?></span>
	<div class="margin-top-0">
		<?php echo esc_html__( 'There are no questions defined for this quiz so the dynamic content cannot be added!', 'thrive-quiz-builder' ); ?>
	</div>
	<div class="m-20 tcb-text-center">
		<button type="button" class="green medium tve-button click" data-fn="redirect" data-href="<?php echo esc_attr( tge()->editor_url( $quiz_post ) ); ?>"><?php echo esc_html__( 'Add Questions', 'thrive-quiz-builder' ) ?></button>
	</div>
<?php elseif ( is_numeric( $absolute_limits['min'] ) && $absolute_limits['min'] == $absolute_limits['max'] ) : // question min = question max; no branches in question editor. ?>
	<?php $quiz_post = get_post( $variation['quiz_id'] ); ?>
	<span class="tcb-modal-title ml-0 mt-0"><?php echo esc_html__( 'There seems to be a problem with your quiz', 'thrive-quiz-builder' ); ?></span>
	<div class="margin-top-0">
		<?php echo esc_html__( 'The minimum and maximum result cannot be the same! First you need to add points to the quiz answers and then add the dynamic content in your page.', 'thrive-quiz-builder' ); ?>
	</div>
	<div class="m-20 tcb-text-center">
		<button type="button" class="green medium tve-button click" data-fn="redirect" data-href="<?php echo esc_attr( tge()->editor_url( $quiz_post ) ); ?>"><?php echo esc_html__( 'Edit Questions', 'thrive-quiz-builder' ) ?></button>
	</div>
<?php else : ?>
	<?php
	$max = Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS;
	$aux = $absolute_limits['max'] - $absolute_limits['min'];
	if ( $aux < $max ) {
		$max = $aux + 1;
	}
	?>
	<span class="tcb-modal-title ml-0 mt-0"><?php echo esc_html__( 'Dynamic content intervals', 'thrive-quiz-builder' ); ?></span>
	<div class="margin-top-0">
		<?php echo sprintf( __( 'Before adding your Dynamic Content, please choose how to split your results into intervals.<br> You can create a maximum of %s intervals', 'thrive-quiz-builder' ), $max ); ?>
	</div>
	<div class="control-grid margin-top-20">
		<span class="tqb-import-content-description tqb-action-required-text"><?php echo esc_html__( 'Split available results range into: ', 'thrive-quiz-builder' ); ?></span>
		<input id="tqb_result_intervals" maxlength="2" class="tve-input change input" type="number" min="1" max="<?php echo esc_attr( $max ); ?>" step="1" data-fn-change="preview_states" data-fn-input="preview_states">
		<span class="tqb-import-content-description tqb-action-required-text"><?php echo esc_html__( 'equal intervals', 'thrive-quiz-builder' ); ?></span>
	</div>
	<div id="tqb-intervals-preview" class="row margin-top-20"></div>
	<div class="m-20">
		<div class="tcb-text-center">
			<button type="button" class="green medium tve-button click" data-fn="save_states_number"><?php echo esc_html__( 'Create new dynamic content intervals', 'thrive-quiz-builder' ) ?></button>
		</div>
	</div>
	<?php if ( tqb_has_similar_dynamic_content( $variation ) ) : ?>
		<?php

		switch ( $variation['post_type'] ) {
			case Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS:
				$searched_post_type = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN;
				break;
			case Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN:
				$searched_post_type = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS;
				break;
			default:
				$searched_post_type = '';
				break;
		}
		$page_to_import_from = tqb()->get_style_page_name( $searched_post_type );

		?>
		<div class="row tqb-line-with-or"></div>
		<div class="tcb-text-center">
			<?php echo sprintf( esc_html__( 'You can choose to import intervals from the %s.', 'thrive-quiz-builder' ), $page_to_import_from ); ?>
		</div>
		<div class="tcb-text-center">
			<?php echo esc_html__( 'If you choose this, only the intervals ranges will be imported.', 'thrive-quiz-builder' ); ?>
		</div>
		<div class="mt-20 mb-30 tcb-text-center">
			<button type="button" class="blue medium tve-button click" data-fn="copy_states_from_prev_page"><?php tcb_icon( 'transfer' ); ?>&nbsp;<?php echo sprintf( esc_html__( 'Import intervals from %s', 'thrive-quiz-builder' ), esc_html( $page_to_import_from ) ); ?></button>
		</div>
	<?php endif; ?>
<?php endif; ?>
