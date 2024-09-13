<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$dynamic_links = [
	'register'    => [ 'label' => 'Register' ],
	'login'       => [ 'label' => 'Log In' ],
	'logout'      => [ 'label' => 'Logout' ],
	'bk_to_login' => [ 'label' => 'Back to Login' ],
	'pass_reset'  => [ 'label' => 'Password Reset' ],
];

foreach ( $dynamic_links as $key => $dynamic_link ) {
	$_link = tcb_get_dynamic_link( $dynamic_link['label'], 'Login Form' );

	if ( $_link ) {
		$dynamic_link['id']  = isset( $_link['id'] ) ? $_link['id'] : '';
		$dynamic_link['url'] = isset( $_link['url'] ) ? $_link['url'] : '#';

		$dynamic_links[ $key ] = $dynamic_link;
	}
}
?>

<div class="thrv_wrapper thrv-login-element" data-ct="login" data-ct-name="Default" data-type="login">
	<div class="tcb-login-form-wrapper tve_empty_dropzone tve_no_drag tve-form-state tve-active-state" data-state="login">
		<div class="thrv_wrapper tcb-login-form tcb-no-clone tcb-no-delete tcb-no-save tve_no_drag">
			<form action="" method="post" novalidate class="tve-login-form">
				<div class="tve-form-drop-zone">
					<div class="tve-login-item-wrapper">
						<div class="tve-login-form-item tcb-no-clone tcb-no-delete tcb-no-save">
							<div class="thrv-form-input-wrapper tcb-no-clone tcb-no-delete tcb-no-save" data-type="email">
								<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
									<div class="tcb-plain-text"><?php echo esc_html__( 'Username or Email Address', 'thrive-cb' ); ?></div>
								</div>

								<div class="tve-login-form-input tcb-no-clone tcb-no-delete tcb-no-save">
									<input type="text" name="username">
								</div>
							</div>
						</div>
						<div class="tve-login-form-item tcb-no-clone tcb-no-delete tcb-no-save">
							<div class="thrv-form-input-wrapper tcb-no-clone tcb-no-delete tcb-no-save" data-type="password">
								<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
									<div class="tcb-plain-text"><?php echo esc_html__( 'Password', 'thrive-cb' ); ?></div>
								</div>
								<div class="tve-login-form-input tcb-no-clone tcb-no-delete tcb-no-save">
									<input type="password" name="password">
								</div>
							</div>
						</div>
						<div class="tve-login-form-item tcb-remember-me-item tcb-no-delete tcb-no-clone tcb-no-save">
							<?php tcb_template( 'controls/lead-generation/lg-gdpr.phtml', [ 'remember_me' => true ], false, 'backbone' ); ?>
						</div>
					</div>

					<div class="thrv_wrapper thrv-button tar-login-submit tar-login-elem-button tcb-no-delete tcb-no-save tcb-no-scroll tcb-no-clone tcb-local-vars-root">
						<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__{"active_palette":0,"config":{"colors":{"62516":{"name":"Main Accent","parent":-1}},"gradients":[]},"palettes":[{"name":"Default Palette","value":{"colors":{"62516":{"val":"rgb(19, 114, 211)","hsl":{"h":210,"s":0.83,"l":0.45}}},"gradients":[]}}]}__CONFIG_colors_palette__</div>
						<a href="javascript:void(0);" class="tcb-button-link tcb-no-delete" data-editable="false">
							<span class="tcb-button-texts tcb-no-clone tve_no_drag tcb-no-save tcb-no-delete"><span class="tcb-button-text thrv-inline-text tcb-no-clone tve_no_drag tcb-no-save tcb-no-delete"><?php esc_html_e( 'Log In', 'thrive-cb' ); ?></span></span>
						</a>
					</div>
					<div class="thrv_wrapper thrv_text_element tcb-lost-password-link tar-login-elem-link tcb-no-title tcb-no-save">
						<p class="tcb-switch-state" data-switch_state="forgot_password" data-shortcode-id="1">
							<a href="javascript:void(0)"
							   class="tve-dynamic-link"
							   data-dynamic-link="thrive_login_form_shortcode"
							   data-shortcode-id="<?php echo esc_attr( $dynamic_links['pass_reset']['id'] ); ?>"
							   data-editable="false"
							><?php esc_html_e( 'I have forgotten my password', 'thrive-cb' ); ?></a>
						</p>
						<p style="text-align: center">
							<?php esc_html_e( "Don't have an account yet?", 'thrive-cb' ); ?>
							<a href="javascript:void(0)"
							   class="tve-dynamic-link"
							   data-dynamic-link="thrive_login_form_shortcode"
							   data-shortcode-id="<?php echo esc_attr( $dynamic_links['register']['id'] ); ?>"
							   data-editable="false"
							><?php esc_html_e( 'Sign up', 'thrive-cb' ); ?></a>
						</p>
					</div>

					<!--Needed for the loader-->
					<button type="submit" style="display: none"></button>
				</div>
			</form>
		</div>
	</div>

	<div class="tcb-login-form-wrapper tve_empty_dropzone tve_no_drag tve-form-state tcb-permanently-hidden" data-state="register">
		<div class="thrv_wrapper tcb-registration-form tcb-no-clone tcb-no-delete tcb-no-save tve_no_drag" data-form-settings="<?php echo esc_attr( json_encode( TCB_Login_Element_Handler::get_registration_form_default_settings() ) ); ?>">
			<form action="" method="post" novalidate class="tve-login-form">
				<div class="tve-form-drop-zone tve_lead_generated_inputs_container">
					<div class="tve-login-item-wrapper tve-form-fields-container">
						<div class="tve-login-form-item tcb-no-clone tcb-no-delete tcb-no-save tve_lg_input_container">
							<div class="thrv-form-input-wrapper tcb-no-clone tcb-no-delete tcb-no-save" data-type="name">
								<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
									<div class="tcb-plain-text"><?php esc_html_e( 'Name', 'thrive-cb' ); ?></div>
								</div>

								<div class="tve-login-form-input tcb-no-clone tcb-no-delete tcb-no-save">
									<input type="text" name="name" data-field="name" data-name="Name" placeholder="<?php esc_attr_e( 'Enter your name', 'thrive-cb' ); ?>">
								</div>
							</div>
						</div>
						<div class="tve-login-form-item tcb-no-clone tcb-no-delete tcb-no-save tve_lg_input_container">
							<div class="thrv-form-input-wrapper tcb-no-clone tcb-no-delete tcb-no-save" data-type="email">
								<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
									<div class="tcb-plain-text"><?php esc_html_e( 'Email', 'thrive-cb' ); ?></div>
								</div>

								<div class="tve-login-form-input tcb-no-clone tcb-no-delete tcb-no-save">
									<input type="email" name="email" data-validation="email" data-required="1" data-field="email" data-name="Email" placeholder="<?php esc_attr_e( 'Enter your email', 'thrive-cb' ); ?>">
								</div>
							</div>
						</div>
						<div class="tve-login-form-item tcb-no-clone tcb-no-delete tcb-no-save tve_lg_input_container">
							<div class="thrv-form-input-wrapper tcb-no-clone tcb-no-delete tcb-no-save" data-type="password">
								<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
									<div class="tcb-plain-text"><?php esc_html_e( 'Password', 'thrive-cb' ); ?></div>
								</div>

								<div class="tve-login-form-input tcb-no-clone tcb-no-delete tcb-no-save">
									<input type="password" name="password" data-required="1" data-field="password" data-name="Password">
									<div class="tve-password-strength-wrapper">
										<div class="tve-password-strength tve-password-strength-0"></div>
										<div class="tve-password-strength tve-password-strength-1"></div>
										<div class="tve-password-strength tve-password-strength-2"></div>
										<div class="tve-password-strength tve-password-strength-3"></div>
										<span class="tve-password-strength-icon"></span>
										<span class="tve-password-strength-text"></span>
									</div>
								</div>
							</div>
						</div>
						<div class="tve-login-form-item tcb-no-clone tcb-no-save tve_lg_input_container">
							<div class="thrv-form-input-wrapper tcb-no-clone tcb-no-delete tcb-no-save" data-type="confirm_password">
								<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
									<div class="tcb-plain-text"><?php esc_html_e( 'Confirm password', 'thrive-cb' ); ?></div>
								</div>

								<div class="tve-login-form-input tcb-no-clone tcb-no-delete tcb-no-save">
									<input type="password" name="confirm_password" data-required="1" data-field="confirm_password" data-name="Confirm password">
								</div>
							</div>
						</div>
					</div>

					<div class="thrv_wrapper thrv-button tar-login-submit tar-login-elem-button tcb-no-delete tcb-no-save tcb-no-clone tcb-local-vars-root">
						<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__{"active_palette":0,"config":{"colors":{"62516":{"name":"Main Accent","parent":-1}},"gradients":[]},"palettes":[{"name":"Default Palette","value":{"colors":{"62516":{"val":"rgb(19, 114, 211)","hsl":{"h":210,"s":0.83,"l":0.45}}},"gradients":[]}}]}__CONFIG_colors_palette__</div>
						<a href="javascript:void(0);" class="tcb-button-link tcb-no-delete" data-editable="false">
							<span class="tcb-button-texts tcb-no-clone tve_no_drag tcb-no-save tcb-no-delete"><span class="tcb-button-text thrv-inline-text tcb-no-clone tve_no_drag tcb-no-save tcb-no-delete"><?php esc_html_e( 'Sign Up', 'thrive-cb' ); ?></span></span>
						</a>
					</div>

					<!--Needed for the loader-->
					<button type="submit" style="display: none"></button>
				</div>
				<input type="hidden" class="tve-lg-err-msg" value="<?php echo esc_attr( json_encode( TCB_Login_Element_Handler::get_registration_error_messages() ) ); ?>">
				<input id="_sendParams" type="hidden" name="_sendParams" value="1">
				<input id="_back_url" type="hidden" name="_back_url" value="#">
			</form>
		</div>

		<div class="thrv_wrapper thrv_text_element tar-login-elem-link tcb-no-title tcb-no-save">
			<p class="tcb-switch-state" data-switch_state="login" data-shortcode-id="1">
				<?php esc_html_e( 'Already have an account?', 'thrive-cb' ); ?>
				<a href="javascript:void(0)"
				   class="tve-dynamic-link"
				   data-dynamic-link="thrive_login_form_shortcode"
				   data-shortcode-id="<?php echo esc_attr( $dynamic_links['login']['id'] ); ?>"
				   data-editable="false"
				><?php esc_html_e( 'Login', 'thrive-cb' ); ?></a>
			</p>
		</div>
	</div>

	<div class="tcb-login-form-wrapper tve-form-state tve_empty_dropzone tcb-permanently-hidden tve_no_drag" data-state="forgot_password">
		<div class="thrv_wrapper tcb-login-form tcb-no-clone tcb-no-delete tcb-no-save tve_no_drag">
			<form action="" method="post" class="tve-login-form" novalidate>

				<div class="tve-form-drop-zone">
					<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box tve-elem-default-pad tcb-no-delete tcb-no-save tcb-no-clone">
						<div class="tve-content-box-background"></div>
						<div class="tve-cb">
							<div class="thrv_wrapper thrv_text_element thrv-form-title" data-tag="h2">
								<h2><?php echo esc_html__( 'Password Reset', 'thrive-cb' ); ?></h2>
							</div>
							<div class="thrv_wrapper thrv_text_element thrv-form-info">
								<p><?php echo esc_html__( 'Please enter your email address. You will receive a link to create a new password via email', 'thrive-cb' ); ?></p>
							</div>
							<div class="tve-cf-item-wrapper">
								<div class="tve-login-form-item tcb-no-clone tcb-no-delete tcb-no-save">
									<div class="thrv-form-input-wrapper" data-type="text">
										<div class="thrv_wrapper tcb-label tcb-removable-label thrv_text_element tcb-no-delete tcb-no-save tcb-no-clone tve_no_drag">
											<div class="tcb-plain-text"><?php echo esc_html__( 'Username or Email Address', 'thrive-cb' ); ?></div>
										</div>
										<div class="tve-login-form-input tcb-no-clone tcb-no-delete tve_no_drag tcb-no-save">
											<input type="text" name="login">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="thrv_wrapper thrv-button tar-login-submit tar-login-elem-button tcb-no-delete tcb-no-save tcb-no-clone tcb-local-vars-root">
						<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__{"active_palette":0,"config":{"colors":{"62516":{"name":"Main Accent","parent":-1}},"gradients":[]},"palettes":[{"name":"Default Palette","value":{"colors":{"62516":{"val":"rgb(19, 114, 211)","hsl":{"h":210,"s":0.83,"l":0.45}}},"gradients":[]}}]}__CONFIG_colors_palette__</div>
						<a href="javascript:void(0);" class="tcb-button-link" data-editable="false">
							<span class="tcb-button-texts"><span class="tcb-button-text thrv-inline-text"><?php esc_html_e( 'Get New Password', 'thrive-cb' ); ?></span></span>
						</a>
					</div>

					<div class="thrv_wrapper thrv_text_element tar-login-elem-link tcb-no-title tcb-no-save">
						<p class="tcb-switch-state" data-switch_state="login" data-shortcode-id="0">
							<a href="javascript:void(0)" class="tve-dynamic-link" data-dynamic-link="thrive_login_form_shortcode" data-shortcode-id="<?php echo esc_attr( $dynamic_links['bk_to_login']['id'] ); ?>" data-editable="false"><?php esc_html_e( 'Back to login', 'thrive-cb' ); ?></a>
						</p>
					</div>

					<!--Needed for the loader-->
					<button type="submit" style="display: none"></button>
				</div>

			</form>
		</div>
	</div>

	<div class="tcb-login-form-wrapper tve-form-state tve_empty_dropzone tcb-permanently-hidden tve_no_drag" data-state="reset_confirmation">

		<div class="tve-form-drop-zone">

			<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box tve-elem-default-pad">
				<div class="tve-content-box-background"></div>
				<div class="tve-cb">
					<div class="thrv_wrapper thrv_text_element thrv-form-title" data-tag="h2">
						<h2><?php echo esc_html__( 'Password Reset', 'thrive-cb' ); ?></h2>
					</div>
					<div class="thrv_wrapper thrv_text_element thrv-form-info">
						<p><?php echo esc_html__( 'The instructions to reset your password are sent to the email address you provided. If you did not receive the email, please check your spam folder as well', 'thrive-cb' ); ?></p>
					</div>
				</div>
			</div>

			<div class="thrv_wrapper thrv_text_element tar-login-elem-link tcb-no-title tcb-no-save">
				<p class="tcb-switch-state" data-switch_state="login" data-shortcode-id="0">
					<a href="javascript:void(0)" class="tve-dynamic-link" data-dynamic-link="thrive_login_form_shortcode" data-shortcode-id="<?php echo esc_attr( $dynamic_links['bk_to_login']['id'] ); ?>" data-editable="false"><?php esc_html_e( 'Back to login', 'thrive-cb' ); ?></a>
				</p>
			</div>

		</div>

	</div>

	<div class="tcb-login-form-wrapper tve-form-state tve_empty_dropzone tcb-permanently-hidden tve_no_drag" data-state="logged_in">

		<div class="tve-form-drop-zone">
			<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box tve-elem-default-pad ">
				<div class="tve-content-box-background"></div>
				<div class="tve-cb">
					<div class="thrv_wrapper thrv_text_element thrv-form-title tar-login-elem-h2" data-tag="h2">
						<h2><?php echo esc_html__( 'You are already logged in', 'thrive-cb' ); ?></h2>
					</div>
				</div>
			</div>

			<div class="thrv_wrapper thrv_text_element tar-login-elem-link tcb-no-title tcb-no-save">
				<p class="tcb-switch-state" data-switch_state="login" data-shortcode-id="0">
					<a href="<?php echo esc_url( $dynamic_links['logout']['url'] ); ?>" class="tve-dynamic-link" data-dynamic-link="thrive_login_form_shortcode" data-shortcode-id="<?php echo esc_attr( $dynamic_links['logout']['id'] ); ?>" data-editable="false"><?php esc_html_e( 'Log Out', 'thrive-cb' ); ?></a>
				</p>
			</div>
		</div>

	</div>
	<input type="hidden" name="config" value="<?php echo esc_attr( base64_encode( serialize( TCB_Login_Element_Handler::get_default_settings() ) ) ); ?>">
</div>
