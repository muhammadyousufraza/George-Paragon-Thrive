<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/19/2018
 * Time: 1:53 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-form_submit-component" class="tve-component" data-view="ContactFormSubmit">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tcb-icon-side-wrapper" data-key="icon_side" data-icon="true" data-view="ButtonGroup"></div>
		<div class="tcb-text-center mt-10" data-icon="true">
			<span class="click tcb-text-uppercase clear-format" data-fn="remove_icon">
				<?php tcb_icon( 'close2' ) ?>&nbsp;<?php echo esc_html__( 'Remove Input Icon', 'thrive-cb' ) ?>
			</span>
		</div>
		<div class="tve-control" data-icon="false"  data-view="ModalPicker"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonWidth"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonAlign"></div>
	</div>
</div>
