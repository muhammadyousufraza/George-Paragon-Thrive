<h4><?php echo esc_html__( "Step 1: Choose Connection Type", 'thrive-dash' ) ?></h4>
<hr class="tve_lightbox_line"/>
<p><?php echo esc_html__( "Choose whether you would like to connect using HTML form code or through an established API connection ?", 'thrive-dash' ) ?></p>
<div class="tve_lightbox_select_holder">
	<select class="" id="connection-type">
		<?php foreach ( $connection_types as $connection_key => $connection_name ) : ?>
			<option value="<?php echo esc_attr( $connection_key ); ?>"><?php echo esc_html( $connection_name ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="tve-sp"></div>
<div class="tve_clearfix">
	<a href="javascript:void(0)" class="tve_click tve_editor_button tve_editor_button_success tve_right"
	   data-ctrl="function:auto_responder.connection_form" data-step2="1">
		<?php echo esc_html__( "Go to the next step", 'thrive-dash' ) ?>
	</a>
</div>
