<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/25/2016
 * Time: 2:52 PM
 */
global $variation;
if ( empty( $variation ) ) {
	$variation = tqb_get_variation( ! empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ? absint( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) : 0 );
}

$quiz_type         = TQB_Post_meta::get_quiz_type_meta( $variation['quiz_id'] );
$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
$intervals         = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );

if ( empty( $intervals ) || ! is_array( $intervals ) ) {
	echo '<div id="tqb-form-states"></div>';

	return;
}

?>

<div id="tqb-form-states">
	<div id="tqb-form-states-wrapper" class="control-grid">
		<?php if ( $quiz_type['type'] == Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) : ?>
			<div class="tqb-interval-info">
				<?php tcb_icon( 'help_outline' ); ?>
				<?php echo esc_html__( 'Results:', 'thrive-quiz-builder' ); ?>
			</div>
			<div class="tqb-tcb-row-container tqb-tcb-row-container-personality">
				<?php $personality_intervals = count( $intervals ); ?>
				<?php foreach ( $intervals as $interval ) : ?>
					<?php
					$interval['post_title'] = str_replace( array( "'", '"' ), '', $interval['post_title'] );
					$html                   = "<input type='hidden' id='tqb-child-id' value='" . $interval['id'] . "' /><div class='tqb-personality-results-popover'><div class='tqb-personality-results-dark-holder'><span class='icon-download'></span><a class='click' data-fn='import_content' href='javascript:void(0);'>" . __( 'Import Content', 'thrive-quiz-builder' ) . '</a></div>' . "<div class='tqb-personality-results-purple-holder'>" . $interval['post_title'] . '</div>' . '</div>';
					?>
					<div class="click tqb-tcb-intervals-item tqb-tcb-intervals-item-personality"
						 style="max-width: <?php echo number_format( ( 100 / $personality_intervals ), 2 ); ?>%"
						 data-toggle="popover"
						 data-placement="top"
						 data-content=" <?php echo esc_attr( $html ); ?>"
						 data-html="true"
						 data-fn="state_click"
						 data-hover-tooltip="<?php echo esc_attr( $interval['post_title'] ); ?>"
						 data-id="<?php echo esc_attr( $interval['id'] ); ?>">
						<div><span><?php echo esc_html( $interval['post_title'] ); ?></span></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php
			//Sort the intervals array
			$flag = array();
			foreach ( $intervals as $key => $interval ) {
				$flag[ $key ] = $interval['tcb_fields']['min'];
			}
			array_multisort( $flag, SORT_ASC, $intervals );

			$intervals_size = count( $intervals );
			?>
			<div class="tqb-interval-info">
				<?php tcb_icon( 'help_outline' ); ?>
				<?php echo esc_html__( 'Results Intervals:', 'thrive-quiz-builder' ); ?>
			</div>
			<div class="tqb-tcb-row-container tqb-intervals">
				<?php foreach ( $intervals as $key => $interval ) : ?>
					<?php
					$prev_min = ( ! empty( $intervals[ $key - 1 ] ) ) ? $intervals[ $key - 1 ]['tcb_fields']['min'] : null;
					$prev_id  = ( ! empty( $intervals[ $key - 1 ] ) ) ? $intervals[ $key - 1 ]['id'] : null;
					$next_max = ( ! empty( $intervals[ $key + 1 ] ) ) ? $intervals[ $key + 1 ]['tcb_fields']['max'] : null;
					$next_id  = ( ! empty( $intervals[ $key + 1 ] ) ) ? $intervals[ $key + 1 ]['id'] : null;
					$html     = "
						<input type='hidden' id='tqb-child-id' value='" . esc_attr( $interval['id'] ) . "' />
						<input type='hidden' id='tqb-child-prev-id' value='" . esc_attr( $prev_id ) . "' />
						<input type='hidden' id='tqb-child-next-id' value='" . esc_attr( $next_id ) . "' />
						<input type='hidden' id='tqb-prev-min' value='" . esc_attr( $prev_min ) . "'/>
						<input type='hidden' id='tqb-next-max' value='" . esc_attr( $next_max ) . "'/>
						";
					$html     .= "<div class='tqb-interval-actions-holder'>";
					if ( $intervals_size < Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS &&
					     ( $interval['tcb_fields']['max'] - $interval['tcb_fields']['min'] > 1 ) && // not equal and not consecutive values
					     $interval['tcb_fields']['width'] > Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE // width to be grater then minimum width
					) :
						$html .= "<a data-fn='state_split' class='click tqb-interval-action-button'  href='javascript:void(0);'><span class='icon-call_split'></span>Split</a>";
					endif;
					if ( $intervals_size > 1 ) :
						$html .= "
							<a data-fn='import_content' class='click tqb-interval-action-button' href='javascript:void(0);'><span class='icon-download'></span>Import</a>

							<a href='javascript:void(0);'
								style='float: right;'
								class='click tqb-interval-action-button'
								data-fn='remove_state'>
								<span class='icon-delete_forever'></span>
							</a>";
					endif;
					$html .= '</div>';
					$html .= "
						<div class='tqb-purple-container'>
							<span class='tqb-intervals-info'>Range</span>
							<div class='tqb-input-range-holder'>
								<input type='text' class='tqb-input-range' id='tqb-range-min' value='" . esc_attr( $interval['tcb_fields']['min'] ) . "'/>
								<input type='text' class='tqb-input-range' id='tqb-range-max' value='" . esc_attr( $interval['tcb_fields']['max'] ) . "'/>
							</div>
							<a data-fn='update_intervals' class='click tqb-apply-intervals' href='javascript:void(0);'>" . esc_html__( 'Apply', 'thrive-quiz-builder' ) . '</a>
						</div>';
					?>
					<div class="click tqb-tcb-intervals-item"
						 data-fn="state_click"
						 data-toggle="popover"
						 data-placement="top"
						 data-content="<?php echo esc_attr( $html ); ?>"
						 data-html="true"
						 data-min="<?php echo esc_attr( $interval['tcb_fields']['min'] ); ?>"
						 data-max="<?php echo esc_attr( $interval['tcb_fields']['max'] ); ?>"
						 data-id="<?php echo esc_attr( $interval['id'] ); ?>"
						 style="width: <?php echo esc_attr( $interval['tcb_fields']['width'] ); ?>px"></div>
				<?php endforeach; ?>
				<div class="tqb-tcb-row-container tve_left tqb-intervals-range">
					<div class="tve_left"><?php echo esc_html( $intervals[0]['tcb_fields']['min'] ); ?></div>
					<?php foreach ( $intervals as $key => $interval ) : ?>
						<div style="width: <?php echo esc_attr( $interval['tcb_fields']['width'] ); ?>px"
							 class="tqb-tcb-numeric-range-preview"
							 data-id="<?php echo esc_attr( $interval['id'] ); ?>">
							<?php if ( $key != 0 ) : ?>
								<div class="tve_left"><?php echo esc_html( $interval['tcb_fields']['min'] ); ?></div>
							<?php endif; ?>
							<?php if ( $key != $intervals_size - 1 ) : ?>
								<div class="tve_right"><?php echo esc_html( $interval['tcb_fields']['max'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div
						class="tve_left"><?php echo esc_html( $intervals[ $intervals_size - 1 ]['tcb_fields']['max'] ); ?></div>
				</div>
			</div>
			<div class="tqb-tcb-row-container tqb-interval-actions">
				<div class="click" data-fn="equalize">
					<?php tcb_icon( 'view_column' ); ?>
					<span
						class="tqb-interval-actions-button"><?php echo esc_html__( 'Equalize sizes', 'thrive-quiz-builder' ); ?></span>
				</div>
				<div class="click" data-fn="reset">
					<?php tcb_icon( 'rotate' ); ?>
					<span
						class="tqb-interval-actions-button"><?php echo esc_html__( 'Reset all', 'thrive-quiz-builder' ); ?></span>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
