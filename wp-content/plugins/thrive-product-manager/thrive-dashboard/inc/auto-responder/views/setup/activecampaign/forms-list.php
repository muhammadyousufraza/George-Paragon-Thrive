<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
 if ( ! empty( $data['forms'] ) ): ?>
	<div class="tve-sp"></div>
	<p class="tl-mock-paragraph"><?php echo esc_html__( 'Choose the form you want to use:', 'thrive-dash' ) ?></p>
	<div class="tvd-row tvd-collapse">
		<div class="tvd-col tvd-s4">
			<div class="tve_lightbox_select_holder tve_lightbox_input_inline tve_lightbox_select_inline tve_activecampaign_select tvd-input-field">
				<?php foreach ( $data['forms'] as $list_id => $forms ): ?>
					<label for="tve-api-extra" class="tve-custom-select">
						<select data-list-id="<?php echo esc_attr( $list_id ); ?>" style="display: none;" class="tve-api-extra tve_disabled tl-api-connection-list" name="activecampaign_form">
							<?php foreach ( $forms as $id => $form ): ?>
								<option value="<?php echo esc_attr( $form['id'] ); ?>" <?php echo ! empty( $data['form'] ) && $data['form'] == $form['id'] ? 'selected' : ''; ?>><?php echo esc_html( $form['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="tve_activecampaign_no_forms">
		<p>
			<?php echo esc_html__( 'No forms available for this list!', 'thrive-dash' ); ?>
		</p>
	</div>
<?php elseif ( ! empty( $this->_error ) ): ?>
	<div class="tve_activecampaign_error">
		<p>
			<?php echo esc_html__( 'No forms available!', 'thrive-dash' ); ?>
		</p>
	</div>
<?php endif; ?>
<div class="tve-sp"></div>
<div class="tvd-v-spacer vs-2"></div>
<div class="tvd-input-field">
	<input id="activecampaign_tags" type="text" class="tve-api-extra tve_lightbox_input tve_lightbox_input_inline" name="activecampaign_tags" value="<?php echo ! empty( $data['tags'] ) ? esc_attr( $data['tags'] ) : '' ?>" size="40"/>
	<label for="activecampaign_tags"><?php echo esc_html__( 'Tags', 'thrive-dash' ) ?></label>
</div>
<?php
$tags_message = __( "Comma-separated lists of tags to assign to a new contact in ActiveCampaign", 'thrive-dash' );
$tags_message = apply_filters( 'tvd_tags_text_for_' . $this->get_key(), $tags_message );
?>
<p><?php echo esc_html( $tags_message ); ?></p>
<script type="text/javascript">
	(
		function ( $ ) {
			$( document ).on( 'change', '#thrive-api-list-select', function () {
				var list_id = $( '#thrive-api-list-select' ).find( ':selected' ).val(),
					select = $( '.tve_activecampaign_select' ),
					no_forms = $( '.tve_activecampaign_no_forms' ),
					no_form_def = $( '.tve_activecampaign_error' ),
					$forms = $( 'select.tve-api-extra[data-list-id="' + list_id + '"]' );
				select.show();
				no_forms.hide();
				$( 'select.tve-api-extra[name="activecampaign_form"]' ).addClass( 'tve_disabled' ).hide().parents( '.tve-custom-select' ).hide();
				if ( $forms.length > 0 ) {
					$forms.removeClass( 'tve_disabled' ).show().parents( '.tve-custom-select' ).show();
					no_form_def.hide();
				} else {
					select.hide();
					no_forms.show();
				}
			} );

			$( '#thrive-api-list-select' ).trigger( 'change' );
		}
	)( jQuery );
</script>
