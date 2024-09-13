<span class="tcb-modal-title ml-0 mt-0"><?php echo esc_html__( 'Insert WordPress Content into the page', 'thrive-cb' ) ?></span>

<div class="pt-10" id="tve_tinymce_shortcode_mce_holder">
	<?php
	tcb_remove_tinymce_conflicts();
	wp_editor( '', 'tve_tinymce_shortcode', [
		'dfw'               => true,
		'tabfocus_elements' => 'insert-media-button,save-post',
		'editor_height'     => 260,
		'textarea_rows'     => 15,
	] );
	?>
</div>

<div class="tcb-modal-footer flex-end pr-0">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save"><?php echo esc_html__( 'Save', 'thrive-cb' ) ?></button>
</div>
