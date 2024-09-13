<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-thrive_comments_form_submit-component" class="tve-component" data-view="CommentFormSubmit">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tcb-icon-side-wrapper" data-key="icon_side" data-icon="true" data-view="ButtonGroup"></div>
		<div class="tcb-text-center mt-10" data-icon="true">
			<span class="click tcb-text-uppercase clear-format" data-fn="remove_icon">
				<?php tcb_icon( 'close2' ) ?>&nbsp;<?php echo __( 'Remove Input Icon', 'thrive-theme' ) ?>
			</span>
		</div>
		<div class="tve-control" data-icon="false" data-view="ModalPicker"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonWidth"></div>
		<hr>
		<div class="tve-control gl-st-button-toggle-2" data-view="ButtonAlign"></div>
		<hr class="tcb-hide">
		<div class="tve-control tcb-hide" data-key="style" data-initializer="button_style_control"></div>
	</div>
</div>
