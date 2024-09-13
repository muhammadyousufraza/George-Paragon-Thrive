<h4><?php echo esc_html__( "Connect with Service", 'thrive-dash' ) ?></h4>
<hr class="tve_lightbox_line"/>
<?php
$connection_config          = $data['connection_config'];
$klicktipp_option           = count( $connection_config ) == 1 && isset( $connection_config['klicktipp'] );
$custom_messages            = is_array( $data['custom_messages'] ) ? $data['custom_messages'] : array();
$custom_messages['error']   = empty( $custom_messages['error'] ) ? '' : $custom_messages['error'];
$custom_messages['success'] = empty( $custom_messages['success'] ) ? '' : $custom_messages['success'];
$create_account             = $data['create_account'];
/**
 * at this stage, we have a list of existing connections that are to be displayed in a list
 */

$available = Thrive_Dash_List_Manager::get_available_apis( true );
$helper    = new Thrive_Dash_Api_CustomHtml();
$form_type = $helper->getFormType();
$form_type !== 'lead_generation' ? $variations = $helper->getFormVariations() : $variations = array();

if ( function_exists( 'tve_leads_get_form_variation' ) ) {
	if ( ! empty( $_POST['_key'] ) ) {
		$this_variation = tve_leads_get_form_variation( null, sanitize_text_field( $_POST['_key'] ) );

		if ( $form_type != 'lightbox' && $this_variation['form_state'] == 'lightbox' ) {
			foreach ( $variations as $key => $variation ) {
				if ( $variation['form_state'] == 'lightbox' ) {
					unset( $variations[ $key ] );
				}
			}
		}
	}
}

?>
<div class="tve_large_lightbox tve_lead_gen_lightbox_small">
	<p><?php echo esc_html__( 'Your sign up form is connected to service(s) using the following API connections:', 'thrive-dash' ) ?></p>
	<table>
        <caption><?php echo esc_html__( 'API connections', 'thrive-dash' ) ?></caption>
		<thead>
		<tr>
			<th scope="col" colspan="2">
				<?php echo esc_html__( "Service Name", 'thrive-dash' ) ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $connection_config as $key => $list_id ) : if ( ! isset( $available[ $key ] ) ) {
			continue;
		} ?>
			<tr>
				<td width="90%">
					<?php echo esc_html( $available[ $key ]->get_title() ); ?>
				</td>
				<td width="10%">
					<a href="javascript:void(0)" class="tve_click" data-ctrl="function:auto_responder.connection_form"
					   data-connection-type="api" data-key="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_html__( "Settings", 'thrive-dash' ) ?>">
						<span class="tve_icm tve-ic-cog tve_ic_small tve_lightbox_icon_small"></span>
					</a>
					&nbsp;&nbsp;&nbsp;
					<a href="javascript:void(0)" class="tve_click" data-ctrl="function:auto_responder.api.remove"
					   data-key="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_html__( "Remove", 'thrive-dash' ) ?>">
						<span class="tve_icm tve-ic-close tve_ic_small tve_lightbox_icon_small"></span>
					</a>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<div class="tve-sp"></div>
	<?php if ( count( $available ) != count( $connection_config ) ) : ?>
		<div class="clearfix">
			<a href="javascript:void(0)" class="tve_click tve_right tve_editor_button tve_editor_button_success"
			   data-ctrl="function:auto_responder.connection_form" data-connection-type="api">
				<?php echo esc_html__( "Add New Connection", 'thrive-dash' ) ?>
			</a>
		</div>
	<?php endif ?>
	<div class="tve_clear" style="height:30px;"></div>
	<p><?php echo esc_html__( 'Select which fields to display and their properties (you can reorder them by dragging the "move" icon from the left):', 'thrive-dash' ) ?></p>
	<?php
	$fields_table       = isset( $data['fields_table'] ) ? $data['fields_table'] : '';
	$show_thank_you_url = true;
	$show_reCaptcha     = true;
	include dirname( __FILE__ ) . '/autoresponder-code-fields.php';

	?>
	<div class="tve-sp"></div>
	<?php if ( ! empty( $data['show_submit_options'] ) ) : ?>
		<div class="tve_gray_box tve_ps_container">
			<h4><?php echo esc_html__( "Action After Signup", 'thrive-dash' ) ?></h4>
			<?php $submit = ! empty( $_POST['submit_option'] ) ? sanitize_text_field( $_POST['submit_option'] ) : 'reload' ?>
			<?php if ( $submit == 'page' ) {
				$thank_you_url = ! empty( $_POST['thank_you_url'] ) ? esc_url_raw( $_POST['thank_you_url'] ) : 0;
				$post          = get_post( $thank_you_url );
			} ?>
			<?php $state = ! empty( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '' ?>
			<label><?php echo esc_html__( "After the form is submitted:", 'thrive-dash' ) ?>&nbsp;</label>

			<div class="tve_lightbox_select_holder tve_lightbox_select_holder_submit tve_lightbox_input_inline tve_lightbox_select_inline">
				<select class="tve_lg_validation_options tve_change tve-api-submit-filters" id="tve-api-submit-option"
						data-ctrl="function:auto_responder.api.submit_option_changed">
					<option <?php echo ! empty( $create_account ) ? 'style="display:none"' : '' ?>
						value="reload"<?php echo $submit == 'reload' ? ' selected="selected"' : '' ?>><?php echo esc_html__( "Reload current page", 'thrive-dash' ) ?>
					</option>
					<option <?php echo ! empty( $create_account ) ? 'style="display:none"' : '' ?>
						value="redirect"<?php echo $submit == 'redirect' ? ' selected="selected"' : '' ?>><?php echo esc_html__( "Redirect to URL", 'thrive-dash' ) ?>
					</option>
					<option <?php echo ! empty( $create_account ) ? 'style="display:none"' : '' ?>
						value="message" <?php echo $submit == 'message' ? ' selected="selected"' : '' ?>><?php echo esc_html__( "Display message without reload", 'thrive-dash' ) ?>
					</option>
					<option
						value="page" <?php echo $submit == 'page' ? ' selected="selected"' : '' ?>><?php echo esc_html__( "Redirect to Page", 'thrive-dash' ) ?>
					</option>
					<?php if ( $form_type !== 'lead_generation' && ! empty( $variations ) ) : ?>
						<option <?php echo ! empty( $create_account ) ? 'style="display:none"' : '' ?>
							value="state" <?php echo $submit == 'state' ? ' selected="selected"' : '' ?>><?php echo esc_html__( "Switch State", 'thrive-dash' ) ?>
						</option>
					<?php endif; ?>
					<?php if ( $klicktipp_option ) : ?>
						<option <?php echo ! empty( $create_account ) ? 'style="display:none"' : '' ?>
							value="klicktipp-redirect" <?php echo $submit == 'klicktipp-redirect' ? ' selected="selected"' : '' ?>><?php echo esc_html__( "KlickTipp Thank You URL", 'thrive-dash' ) ?>
						</option>
					<?php endif; ?>

				</select>
			</div>
			<input <?php echo $submit !== 'redirect' ? ' style="display: none"' : '' ?> size="70"
																						class="tve_change tve_text tve_lightbox_input tve_lightbox_input_inline tve_lightbox_input_inline_redirect"
																						data-ctrl="function:auto_responder.api.thank_you_url"
																						value="<?php echo ! empty( $_POST['thank_you_url'] ) ? sanitize_text_field( esc_attr( $_POST['thank_you_url'] ) ) : '' ?>"
																						placeholder="http://"/>
			<input <?php echo $submit !== 'page' ? ' style="display: none"' : '' ?> size="70"
																					class="tve_change tve_text tve_lightbox_input tve_lightbox_input_inline tve-api-pages"
																					data-ctrl="function:auto_responder.api.set_page_search"
																					value="<?php echo isset( $post ) ? esc_attr( $post->post_title ) : '' ?>"
																					placeholder="Search for pages or posts"/>

			<div class="tve_message_settings" <?php echo $submit !== 'message' ? ' style="display: none"' : '' ?>>
				<p><?php echo esc_html__( "The following message will be displayed in a small popup after signup, without reloading the page.", 'thrive-dash' ) ?></p>
				<div class="tve_dashboard_tab_success tve_dashboard_tab tve_dashboard_tab_selected">
					<?php wp_editor( $custom_messages['success'], 'tve_success_wp_editor', $settings = array(
						'quicktags'     => false,
						'media_buttons' => false
					) ); ?>
				</div>
			</div>
			<?php if ( $form_type !== 'lead_generation' ) : ?>
				<div class="tve_state_settings" <?php echo $submit !== 'state' ? ' style="display: none"' : '' ?>>
					<?php if ( ! empty( $variations ) ) : ?>
						<label><?php echo esc_html__( "Choose the state to switch :", 'thrive-dash' ) ?>&nbsp;</label>
						<div class="tve_lightbox_select_holder tve_lightbox_input_inline tve_lightbox_select_inline">
							<select class="tve_change_states tve_change" data-ctrl="function:auto_responder.api.state_changed">
								<?php foreach ( $variations as $variation ) : ?>
									<option data-state="<?php echo esc_attr( $variation['form_state'] ); ?>"
											value="<?php echo esc_attr( $variation['key'] ); ?>" <?php echo $state == $variation['key'] ? ' selected="selected"' : '' ?>><?php echo esc_html( $variation['state_name'] ); ?>
									</option>
								<?php endforeach; ?>

							</select>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<div
			class="tve-shortcodes-wrapper tve-shortcodes-message" <?php echo $submit == 'message' && ! empty( $_POST['error_message_option'] ) && sanitize_text_field( $_POST['error_message_option'] ) != 1 ? '' : 'style="display:none"' ?>>
			<?php include dirname( __FILE__ ) . '/partials/api-shortcodes.php'; ?>
		</div>
		<div class="tve-sp"></div>
		<div class="tve-error-message-option tve_lightbox_input_holder">
			<input type="checkbox" <?php echo ! empty( $_POST['error_message_option'] ) && absint( $_POST['error_message_option'] ) == 1 ? 'checked' : '' ?> class="tve_change" id="tve-error-message-option"
				data-ctrl="function:auto_responder.error_message_option_changed"/>
			<label for="tve-error-message-option"><?php echo esc_html__( "Add Error Message", 'thrive-dash' ) ?></label>
		</div>
		<div class="tve_gray_box tve-error-message-wrapper" <?php echo ! empty( $_POST['error_message_option'] ) && absint( $_POST['error_message_option'] ) != 1 ? 'style="display:none"' : '' ?>>
			<h4><?php echo esc_html__( "Edit your error message", 'thrive-dash' ) ?></h4>
			<p><?php echo esc_html__( "This error message is shown in the rare case that the signup fails. This can happen when your connected email marketing service can't be reached.", 'thrive-dash' ) ?>
			</p>
			<div class="tve_dashboard_tab_error">
				<?php wp_editor( $custom_messages['error'], 'tve_error_wp_editor', $settings = array(
					'quicktags'     => false,
					'media_buttons' => false
				) ); ?>
			</div>
		</div>
		<div class="tve-shortcodes-wrapper tve-shortcodes-error" <?php echo ! empty( $_POST['error_message_option'] ) && absint( $_POST['error_message_option'] ) != 1 ? 'style="display:none"' : '' ?>>
			<?php include dirname( __FILE__ ) . '/partials/api-shortcodes.php'; ?>
		</div>
	<?php endif ?>

	<div class="tve-sp"></div>
	<div class="tve_clearfix">
		<a href="javascript:void(0)" class="tve_click tve_editor_button tve_editor_button_default tve_right tve_button_margin"
		   data-ctrl="function:controls.lb_close">
			<?php echo esc_html__( "Cancel", 'thrive-dash' ) ?>
		</a>
		&nbsp;
		<a href="javascript:void(0)" class="tve_click tve_editor_button tve_editor_button_success tve_right"
		   data-ctrl="function:auto_responder.save_api_connection" data-edit-custom="1">
			<?php echo esc_html__( "Save", 'thrive-dash' ) ?>
		</a>
	</div>
</div>
