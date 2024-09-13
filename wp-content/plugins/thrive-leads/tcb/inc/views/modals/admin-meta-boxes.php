<div class="tcb-meta-boxes-modal-title"><?php echo __( 'WordPress settings', 'thrive-cb' ); ?></div>

<div class="ml-30 mr-30 mt-20 mb-20">
	<select class="tcb-meta-boxes-select" name="meta-boxes-select" multiple></select>
</div>

<div class="tcb-editor-meta-boxes-wrapper">
	<iframe id="tcb-editor-meta-boxes-iframe" data-src="<?php echo add_query_arg( TCB_Editor_Meta_Boxes::FLAG, 1, get_edit_post_link() ); ?>"></iframe>
</div>

<div class="tcb-meta-boxes-modal-footer">
	<button class="tve-button medium grey ghost click" data-fn="close"><?php echo __( 'Cancel', 'thrive-cb' ); ?></button>
	<button class="tve-button medium green click" data-fn="triggerSave"><?php echo __( 'Save', 'thrive-cb' ); ?></button>
</div>
