<div id="thrive-api-connections">
	<?php if ( empty( $connected_apis ) ) : ?>
		<h6><?php echo esc_html__( "You currently don't have any API integrations set up.", 'thrive-dash' ) ?></h6>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_api_connect' ) ); ?>" target="_blank"
		   class="tve_lightbox_link tve_lightbox_link_create"><?php echo esc_html__( "Click here to set up a new API connection", 'thrive-dash' ) ?></a>
	<?php else : ?>
		<h6><?php echo esc_html__( "Choose from your list of existing API connections or", 'thrive-dash' ) ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_api_connect' ) ); ?>" target="_blank" class="tve_lightbox_link tve_lightbox_link_create">
				<?php echo esc_html__( 'add a new integration', 'thrive-dash' ) ?>
			</a>
		</h6>
		<div class="tve_lightbox_select_holder tve_lightbox_input_inline tve_lightbox_select_inline">
			<select id="thrive-api-connections-select" class="tve_change"
					data-ctrl="function:auto_responder.api.api_get_lists" autocomplete="off">
				<?php foreach ( $connected_apis as $key => $api ) : ?>
					<option
						value="<?php echo esc_attr( $key ) ?>"<?php echo $edit_api_key == $key ? ' selected="selected"' : '' ?>><?php echo esc_html( $api->get_title() ); ?></option>
				<?php endforeach ?>
			</select>
		</div>
		&nbsp;&nbsp;&nbsp;
		<a href="javascript:void(0)" data-ctrl="function:auto_responder.api.reload_apis" class="tve_click tve_lightbox_link tve_lightbox_link_refresh"><?php echo esc_html__( "Reload", 'thrive-dash' ) ?></a>
	<?php endif ?>
</div>
