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
<div id="tve-lead_generation_checkbox-component" class="tve-component" data-view="LeadGenerationCheckbox">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control" data-key="Required" data-view="Checkbox"></div>
		<div class="tve-control hide-mobile hide-tablet" data-view="ColumnNumber"></div>
		<div class="tve-control" data-view="VerticalSpace"></div>
		<div class="tve-control hide-tablet hide-mobile" data-view="HorizontalSpace"></div>
		<hr class="option-heading">
		<div class="control-grid option-heading">
				<span class="label">
					<?php echo esc_html__( 'Options', 'thrive-cb' ); ?>
				</span>
			<button class="tve-button blue click" data-fn="addOption"><?php echo esc_html__( 'Add', 'thrive-cb' ); ?></button>
		</div>
		<div id="option-list" class="no-space"></div>
		<div class="tve-control" data-view="AnswerTag"></div>
	</div>
</div>
