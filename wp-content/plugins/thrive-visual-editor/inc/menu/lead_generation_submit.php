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
<div id="tve-lead_generation_submit-component" class="tve-component" data-view="LeadGenerationSubmit">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tcb-icon-side-wrapper hide-states" data-key="icon_side" data-icon="true" data-view="ButtonGroup"></div>
		<div class="tcb-text-center mt-10 hide-states" data-icon="true">
			<span class="click tcb-text-uppercase clear-format" data-fn="remove_icon">
				<?php tcb_icon( 'close2' ) ?>&nbsp;<?php echo esc_html__( 'Remove Input Icon', 'thrive-cb' ) ?>
			</span>
		</div>
		<div class="tve-control gl-st-button-toggle-2" data-icon="false" data-view="ModalPicker"></div>
		<hr class="hide-states" data-icon="true">
		<hr data-icon="false">
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonWidth"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonAlign"></div>
		<hr>
	</div>
</div>
