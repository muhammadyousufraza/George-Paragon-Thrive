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
<div id="tve-assessment-component" class="tve-component" data-view="Assessment">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main options', 'thrive-apprentice' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center">
			<button class="tve-button orange mb-10 click" data-fn="editElement">
				<?php echo __( 'Edit Assessment Design', 'thrive-apprentice' ); ?>
			</button>
		</div>
		<hr>
		<div class="tve-control mt-10" data-view="Palettes"></div>
		<hr>
		<div class="tve-control mt-10 tcb-relative" data-view="AssessmentSelect"></div>
		<div class="tve-control mt-10" data-key="FormType"></div>
		<div id="assessment-form-type-text" class="info-text hide-tablet hide-mobile">
			<?php echo __( 'Setting the default type to "Auto" will automatically choose the appropriate state based on the members submission status for this assessment', 'thrive-apprentice' ); ?>
		</div>
		<div class="tve-control mt-10" data-key="Align" data-view="ButtonGroup"></div>
		<div class="tve-control mt-10" data-view="FormWidth"></div>
		<div class="tve-advanced-controls extend-grey">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'After successful submission', 'thrive-apprentice' ); ?>
				</span>
			</div>
			<div class="dropdown-content pt-0 overflow-visible">
				<div class="tve-assessment-options-wrapper mt-10 click" data-fn="setAfterSubmitAction">
					<div class="input">
						<a href="javascript:void(0)" class="click style-input flex-start dots">
							<span class="preview"></span>
							<span class="submit-value tcb-truncate t-80"></span>
							<span class="mr-5">
							<?php tcb_icon( 'pen-regular' ); ?>
						</span>
						</a>
					</div>
				</div>
				<div class="assessment-post-submit"></div>
				<div class="tve-control success-message-switch mt-10" data-view="Switch" data-info="true" data-key="showSuccess" data-iconside="top" data-label="<?php esc_attr_e( 'Show success message', 'thrive-apprentice' ); ?>"
					 data-icontooltip="<?php echo esc_attr__( 'Your success message will be displayed after your nominated page is loaded', 'thrive-apprentice' ); ?>"></div>
				<div class="tve-control message-preview" data-view="LabelInputIcon" data-key="successMessage" data-placeholder="Success" data-label="false"
					 data-icontooltip="<?php echo esc_attr__( 'Preview success message', 'thrive-apprentice' ); ?>" data-iconside="top"></div>
			</div>
		</div>
		<button class="tve-button blue long click mt-10" data-fn="manageErrorMessages">
			<?php echo esc_html__( 'Edit error messages', 'thrive-apprentice' ) ?>
		</button>
	</div>
</div>
