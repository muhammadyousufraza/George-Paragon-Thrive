<h2 class="tcb-modal-title"><?php echo esc_html__( 'Set error message' ) ?></h2>

<div class="tcb-fields-error control-grid wrap"></div>

<div class="control-grid">
	<button type="button" class="tve-button text-only click" data-fn="restore_defaults">
		<?php tcb_icon( 'close' ) ?>
		<?php echo esc_html__( 'Restore errors to default' ) ?>
	</button>
</div>

<div class="tcb-modal-footer">
	<button type="button" class="tcb-left tve-button text-only tcb-modal-cancel">
		<?php echo esc_html__( 'Cancel', 'thrive-cb' ) ?>
	</button>
	<button type="button" class="tcb-right medium tve-button tcb-modal-save">
		<?php echo esc_html__( 'Save', 'thrive-cb' ) ?>
	</button>
</div>
