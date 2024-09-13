<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-lead_generation_select-component" class="tve-component" data-view="LeadGenerationSelect">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control gl-st-button-toggle-1" data-view="DropdownPalettes"></div>
		<div class="tve-control tve-style-options no-space preview" data-view="StyleChange"></div>
		<div class="tve-control" data-key="SelectStylePicker" data-initializer="selectStylePicker"></div>
		<hr>
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control" data-key="Required" data-view="Checkbox"></div>
		<div class="tve-control mt-10" data-view="RowsWhenOpen"></div>
		<div class="tve-control" data-view="Placeholder"></div>
		<div class="tve-control" data-view="PlaceholderInput"></div>
		<div class="tve-control" data-key="DropdownIcon" data-initializer="dropdownIcon"></div>
		<div class="tve-control if-not-hamburger" data-view="DropdownAnimation"></div>
		<div class="tve-control" data-view="AnswerTag"></div>
		<div class="tve-advanced-controls">
			<div class="dropdown-header" data-prop="advanced">
				<span><?php echo esc_html__( 'Manage multiple options', 'thrive-cb' ); ?></span>
			</div>
			<div class="dropdown-content pt-0">
				<div class="tve-lg-invalid-string pb-10 tcb-hidden">
					<div class="info-text red">
						<span><?php echo esc_html__( 'There are errors in your dropdown list values:', 'thrive-cb' ); ?>	</span>
						<div class="flex row ml-15 mb-10 mt-10">
							<span class="tve-lg-invalid-data tve-lg-invalid-label tcb-hidden"><?php echo esc_html__( 'Duplicate labels detected', 'thrive-cb' ); ?>	</span>
							<span class="tve-lg-invalid-data tve-lg-invalid-value tcb-hidden"><?php echo esc_html__( 'Incorrect data on one or more rows', 'thrive-cb' ); ?></span>
						</div>
						<span><?php echo esc_html__( 'Please resolve and update again', 'thrive-cb' ); ?>	</span>
					</div>
				</div>
				<div class="tve-control" data-view="MultipleOptions"></div>
				<button class="tve-button blue long click" data-fn="buildOptionsFromInput"><?php echo esc_html__( 'Apply changes', 'thrive-cb' ); ?></button>
			</div>
		</div>

	</div>
</div>
