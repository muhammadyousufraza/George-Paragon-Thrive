<h4><?php echo esc_html__( "Step 2: Choose your API Connection", 'thrive-dash' ) ?></h4>
<hr class="tve_lightbox_line"/>
<?php
include dirname( __FILE__ ) . '/partials/api-select.php';

include dirname( __FILE__ ) . '/partials/api-lists.php'; ?>

<?php if ( ! empty( $connected_apis ) ) : ?>
	<div class="tve-sp"></div>
	<div class="tve-sp"></div>
	<div id="tve-save-api tve_clearfix">
		<a href="javascript:void(0)" class="tve_click tve_editor_button tve_editor_button_default tve_button_margin tve_right" data-ctrl="function:auto_responder.dashboard"
		   data-edit="<?php echo esc_attr( $edit_api_key ); ?>">
			<?php echo esc_html__( "Cancel", 'thrive-dash' ) ?>
		</a>
		<a href="javascript:void(0)" class="tve_click tve_editor_button tve_editor_button_success tve_right" data-ctrl="function:auto_responder.api.save"
		   data-edit="<?php echo esc_attr( $edit_api_key ); ?>">
			<?php echo esc_html__( "Save", 'thrive-dash' ) ?>
		</a>
	</div>
<?php endif ?>
