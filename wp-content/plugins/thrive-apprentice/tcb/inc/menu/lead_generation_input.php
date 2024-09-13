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
<div id="tve-lead_generation_input-component" class="tve-component" data-view="LeadGenerationInput">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control tcb-icon-side-wrapper" data-key="icon_side" data-icon="true" data-view="ButtonGroup"></div>
		<div class="tcb-text-center mt-10" data-icon="true">
			<span class="click clear-format" data-fn="remove_icon">
				<?php tcb_icon( 'close2' ); ?>&nbsp;<?php echo esc_html__( 'Remove Input Icon', 'thrive-cb' ); ?>
			</span>
		</div>
		<div class="tve-control" data-icon="false" data-view="ModalPicker"></div>
		<hr>
		<div class="tve-control" data-key="placeholder" data-view="LabelInput"></div>
		<hr>
		<div class="tve-control" data-key="required" data-view="Checkbox"></div>
	</div>
</div>
