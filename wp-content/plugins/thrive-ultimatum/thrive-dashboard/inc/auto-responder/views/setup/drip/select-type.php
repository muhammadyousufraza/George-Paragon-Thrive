<div class="tve-sp"></div>
<h6><?php echo esc_html__( 'Choose the type of Drip integration you would like to use', 'thrive-dash' ) ?></h6>
<div class="tvd-row tvd-collapse">
	<div class="tvd-col tvd-s4">
		<?php ?>
		<div class="tve_lightbox_select_holder tve_lightbox_input_inline tve_lightbox_select_inline tvd-input-field">
			<label for="tve-api-extra" class="tve-custom-select">
				<select class="tve-api-extra tl-api-connection-list tve_change" name="drip_type" data-ctrl="auto_responder.api.change_integration_type">
					<option value="list" <?php isset( $data['type'] ) ? selected( $data['type'], 'list' ) : ''; ?>><?php echo esc_html__( 'Mailing List', 'thrive-dash' ) ?></option>
					<option value="automation" <?php isset( $data['type'] ) ? selected( $data['type'], 'automation' ) : ''; ?>><?php echo esc_html__( 'Drip Automation', 'thrive-dash' ) ?></option>
				</select>
			</label>
		</div>
	</div>
</div>

<br>
<p><?php echo esc_html__( '(You can select from having a Mailing List integration or use Drip Automation services which allow you to create actions from events with custom proprieties sent trough the API)', 'thrive-dash' ) ?></p>
