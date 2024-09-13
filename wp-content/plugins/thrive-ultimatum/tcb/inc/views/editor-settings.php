<div class="side">
	<?php if ( tcb_editor()->has_revision_manager() ) : ?>
		<a class="click" href="javascript:void(0)" data-fn="revisions"
		   data-tooltip="<?php echo esc_attr__( 'Revision Manager', 'thrive-cb' ); ?>" data-position="top">
			<?php tcb_icon( 'clock-regular' ); ?>
		</a>
	<?php endif ?>
	<a class="click tve-disabled" id="tcb-undo" href="javascript:void(0)" data-fn="undo" data-position="top"
	   data-tooltip="<?php echo esc_attr__( 'Undo', 'thrive-cb' ); ?>">
		<?php tcb_icon( 'undo-regular' ); ?>
	</a>
	<a class="click tve-disabled" id="tcb-redo" href="javascript:void(0)" data-fn="redo" data-position="top"
	   data-tooltip="<?php echo esc_attr__( 'Redo', 'thrive-cb' ); ?>">
		<?php tcb_icon( 'redo-regular' ); ?>
	</a>

</div>
<div class="side save">
	<a href="javascript:void(0)" class="save-btn click save" data-fn="save">
		<span class="txt"><?php echo esc_html__( 'Save work', 'thrive-cb' ); ?></span>
		<span class="drop click" data-fn="click_save_arrow"><?php tcb_icon( 'chevron-up-regular' ); ?></span>
	</a>
	<a href="javascript:void(0)" class="save-btn click edit-mode p-0 tve-button orange" data-fn="f:main.EditMode.exit"><?php echo esc_html__( 'DONE', 'thrive-cb' ); ?></a>
	<a href="javascript:void(0)" class="save-btn click jump-mode p-0 tve-button orange" data-fn="f:jumplinks.cancel"><?php echo esc_html__( 'EXIT', 'thrive-cb' ); ?></a>
</div>

<div class="save-options">
	<div class="save-options-dropdown">
		<a href="<?php echo esc_url( tcb_get_preview_url( false, false ) ); ?>" class="click" data-fn="save_exit"><?php esc_html_e( 'Save and Preview', 'thrive-cb' ); ?></a>
		<a id="tve-save-dash-return" href="<?php echo esc_url( tcb_get_default_edit_url() ); ?>" class="click" data-fn="save_exit"></a>
		<a class="click" data-fn="toggle_without_save_options"><?php esc_html_e( 'Exit without saving', 'thrive-cb' ); ?></a>
	</div>
	<div class="exit-without-save">
		<div class="center-text confirm-exit">
			<span> <?php echo esc_html__( 'Are you sure you want to', 'thrive-cb' ) ?> </span>
			<span class="confirm-exit-bold"><?php echo esc_html__( ' exit without saving?', 'thrive-cb' ) ?> </span>
		</div>
		<div class="action-buttons">
			<a href="<?php echo esc_url( tcb_get_preview_url( false, false ) ); ?>" data-fn="exit_without_save" class="click tve-button btn-apply"><?php echo esc_html__( 'Yes, exit', 'thrive-cb' ) ?></a>
			<button data-fn="cancel_exit" class="click tve-button btn-cancel"><?php echo esc_html__( 'Cancel', 'thrive-cb' ) ?></button>
		</div>
	</div>
</div>
