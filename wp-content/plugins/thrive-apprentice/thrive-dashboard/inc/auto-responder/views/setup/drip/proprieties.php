<div class="tve-custom-proprieties-container tve-api-option-group tve-api-option-group-automation" <?php echo ! isset( $data['type'] ) || $data['type'] == 'list' ? 'style="display: none;"' : ''; ?>>

	<div class="tve-sp"></div>
	<h6><?php echo esc_html__( 'Choose the proprieties you would like to attach to the call. You can only use underscores and dashes in the custom field input.', 'thrive-dash' ) ?></h6>
	<div class="tve-custom-fields-container">
		<div class="tvd-row tvd-collapse tve-custom-fields">
			<?php
			$proprieties = isset( $data['proprieties'] ) ? $data['proprieties'] : array();
			reset( $proprieties );
			$first_key = key( $proprieties );
			?>
			<div class="tvd-col tvd-s6">
				<div class="tvd-input-field">
					<p><?php echo esc_html__( 'Custom Field', 'thrive-dash' ) ?></p>
					<input type="text"
						   class="drip-custom-field tve_disabled tve_lightbox_input tve_lightbox_input_inline tve_change"
						   name="drip_field" data-ctrl="auto_responder.api.change_input_name"
						   value="<?php echo ! empty( $first_key ) ? esc_attr( $first_key ) : 'thrive_custom_field' ?>"/>
				</div>
			</div>

			<div class="tvd-col tvd-s6">
				<div class="tvd-input-field">
					<p><?php echo esc_html__( 'Custom Field Value', 'thrive-dash' ) ?></p>
					<input type="text"
						   class="drip-custom-field-value tve-api-extra tve_lightbox_input tve_lightbox_input_inline"
						   name="drip_field[<?php echo ! empty( $first_key ) ? esc_attr( $first_key ) : 'thrive_custom_field' ?>]"
						   value="<?php echo ! empty( $proprieties[ $first_key ] ) ? esc_attr( $proprieties[ $first_key ] ) : 'Custom Field Value' ?>"
					/>
				</div>
			</div>
		</div>
		<?php if ( ! empty( $proprieties ) && count( $proprieties ) > 1 ) : ?>
			<?php array_shift( $proprieties ); ?>
			<?php foreach ( $proprieties as $field => $field_value ) : ?>
				<div class="tvd-row tvd-collapse tve-custom-fields">
					<div class="tvd-col tvd-s6">
						<div class="tvd-input-field">
							<p><?php echo esc_html__( 'Custom Field', 'thrive-dash' ) ?></p>
							<input type="text"
								   class="drip-custom-field tve-api-extra-excluded tve_lightbox_input tve_lightbox_input_inline tve_change"
								   name="drip_field" data-ctrl="auto_responder.api.change_input_name"
								   value="<?php echo esc_attr( $field ); ?>"
							/>
						</div>
					</div>

					<div class="tvd-col tvd-s6">
						<div class="tvd-input-field">
							<p><?php echo esc_html__( 'Custom Field Value', 'thrive-dash' ) ?></p>
							<input type="text"
								   class="drip-custom-field-value tve-api-extra tve_lightbox_input tve_lightbox_input_inline"
								   name="drip_field[<?php echo esc_attr( $field ) ?>]"
								   value="<?php echo esc_attr( $field_value ); ?>"
							/>
						</div>
					</div>
					<div class="tvd-col tvd-s6">
						<a href="javascript:void(0)" data-ctrl="auto_responder.api.remove_field"
						   class="tve_click tve_lightbox_link tve_lightbox_link_remove"><?php echo esc_html__( 'Remove', 'thrive-dash' ) ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

	</div>
	<div class="tvd-row tvd-collapse">
		<div class="tvd-col tvd-s2">
			<a href="javascript:void(0)" data-ctrl="auto_responder.api.add_new_field" class="tve_click tve_lightbox_link tve_lightbox_link_create">
				<?php echo esc_html__( 'Add a new custom field', 'thrive-dash' ) ?>
			</a>
		</div>
	</div>
</div>





