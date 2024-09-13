<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$captcha_api       = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
$captcha_available = ! empty( $captcha_api['site_key'] );
?>
<div class="tve_lead_captcha_settings">
	<div class="tve_lightbox_input_holder">
		<input class="tve_lg_validation_options tve_change"
		       type="checkbox"
		       name="tve_api_use_captcha"
		       data-ctrl="function:auto_responder.use_captcha_changed" id="tve-captcha"
			<?php echo $captcha_available ? '' : ' disabled'; ?>
		>
		<label for="tve-captcha">
			<?php			echo esc_html__( 'Add Captcha to Prevent Spam Signups', 'thrive-dash' );
			if ( ! $captcha_available ) {
				echo '(<a href="' . esc_url(admin_url( 'admin.php?page=tve_dash_api_connect' )) . '">' . esc_html__( 'Requires integration with Google ReCaptcha', 'thrive-dash' ) . ')</a>';
			}
			?>
		</label>
	</div>
	<div class="tve_captcha_options" style="display:none;">
		<label><?php echo esc_html__( 'Theme', 'thrive-dash' ); ?>:</label>

		<div class="tve_lightbox_select_holder tve_captcha_option">
			<select class="tve_captcha_theme tve_change" data-option="captcha_theme" data-ctrl="function:auto_responder.captcha_option_changed">
				<option value="light"><?php echo esc_html__( 'Light', 'thrive-dash' ); ?></option>
				<option value="dark"><?php echo esc_html__( 'Dark', 'thrive-dash' ); ?></option>
			</select>
		</div>


		<label><?php echo esc_html__( 'Type', 'thrive-dash' ); ?>:</label>

		<div class="tve_lightbox_select_holder tve_captcha_option">
			<select class="tve_captcha_type tve_change" data-option="captcha_type" data-ctrl="function:auto_responder.captcha_option_changed">
				<option value="image"><?php echo esc_html__( 'Image', 'thrive-dash' ); ?></option>
				<option value="audio"><?php echo esc_html__( 'Audio', 'thrive-dash' ); ?></option>
			</select>
		</div>


		<label><?php echo esc_html__( 'Size', 'thrive-dash' ); ?>:</label>

		<div class="tve_lightbox_select_holder tve_captcha_option">
			<select class="tve_captcha_size tve_change" data-option="captcha_size" data-ctrl="function:auto_responder.captcha_option_changed">
				<option value="normal"><?php echo esc_html__( 'Normal', 'thrive-dash' ); ?></option>
				<option value="compact"><?php echo esc_html__( 'Compact', 'thrive-dash' ); ?></option>
			</select>
		</div>

	</div>
</div>
