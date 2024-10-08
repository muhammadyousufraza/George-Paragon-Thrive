<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/** var $this Thrive_Dash_List_Connection_SendLayer */
?>
<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>

		<div class="tvd-input-field">
			<input id="tvd-mg-api-key" type="text" name="connection[key]" value="<?php echo esc_attr( $this->param( 'key' ) ); ?>">
			<label for="tvd-mg-api-key"><?php echo esc_html__( "API key", 'thrive-dash' ) ?></label>
		</div>
	</form>
</div>
<div class="tvd-row">
	<div class="tvd-col tvd-12">
		<p class="tve-form-description tvd-note-text">
			<a href="https://help.thrivethemes.com/en/articles/8707465-setting-up-an-api-connection-with-sendlayer" target="_blank"><?php echo esc_html__( 'I need help with this', 'thrive-dash' ) ?></a>.
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
