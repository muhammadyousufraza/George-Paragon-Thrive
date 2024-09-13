<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$admin_email = get_option( 'admin_email' );
?>
<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-mg-api-domain" type="text" name="connection[domain]"
			       value="<?php echo esc_attr( $this->param( 'domain' ) ); ?>">
			<label
				for="tvd-mg-api-domain"><?php echo esc_html__( "SparkPost-verified email", 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-pm-api-key" type="text" name="connection[key]"
			       value="<?php echo esc_attr( $this->param( 'key' ) ); ?>">
			<label for="tvd-pm-api-key"><?php echo esc_html__( "API key", 'thrive-dash' ) ?></label>
		</div>
		<?php $this->display_video_link(); ?>
	</form>
</div>
<div class="tvd-row">
	<div class="tvd-col tvd-12">
		<p class="tve-form-description tvd-note-text">
			<?php echo esc_html__( 'Note: Sending through SparkPost only works if your domain name has been set and verified within your SparkPost account. You can use "testing@sparkpostbox.com" as verified email to test your application while waiting for your own to be verified.', 'thrive-dash' ) ?>
			<a href="https://support.sparkpost.com/customer/portal/articles/1933360-verify-sending-domains"
			   target="_blank"><?php echo esc_html__( 'Learn more', 'thrive-dash' ) ?></a>.
		</p>
	</div>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( "Cancel", 'thrive-dash' ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo esc_html__( "Connect", 'thrive-dash' ) ?></a>
		</div>
	</div>
</div>
