<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
 $admin_email = get_option( 'admin_email' ); ?>
<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ) ?></h2>
<div class="tvd-row">
	<p>
		<strong><?php echo esc_html__( 'Note:', 'thrive-dash' ) ?> </strong><?php echo esc_html__( 'Connecting to Campaign Monitor Email Service will also connect to Campaign Monitor autoresponder.', 'thrive-dash' ) ?>
	</p>
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ) ?>"/>
		<input name="connection[new_connection]" type="hidden" value="1"/>
		<div class="tvd-input-field">
			<input id="tvd-aw-api-email" type="text" name="connection[email]"
				   value="<?php echo esc_attr( $this->param( 'email', $admin_email ) ) ?>">
			<label for="tvd-aw-api-email" class="tvd-active"><?php echo esc_html__( 'Campaign Monitor-approved email address', 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-mm-api-key" type="text" name="connection[key]"
				   value="<?php echo esc_attr( $this->param( 'key' ) ) ?>">
			<label for="tvd-mm-api-key"><?php echo esc_html__( 'API key', 'thrive-dash' ) ?></label>
		</div>
		<?php $this->display_video_link(); ?>
	</form>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( 'Cancel', 'thrive-dash' ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo esc_html__( 'Connect', 'thrive-dash' ) ?></a>
		</div>
	</div>
</div>

