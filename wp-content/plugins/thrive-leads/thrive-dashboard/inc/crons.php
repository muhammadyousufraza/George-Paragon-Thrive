<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

add_action( 'init', 'thrive_maybe_schedule_token_cron' );

function thrive_maybe_schedule_token_cron() {
	/**
	 * Schedule token cron
	 */
	if ( ! wp_get_schedule( 'thrive_token_cron' ) ) {
		wp_schedule_event( time(), 'daily', 'thrive_token_cron' );
	}
}

add_action( 'thrive_token_cron', 'thrive_execute_token_job' );

/**
 * Delete token option and thrive admin user when it reaches expiration date
 */
function thrive_execute_token_job() {
	$saved_token = get_option( 'thrive_token_support' );

	if ( isset( $saved_token['valid_until'] ) && $saved_token['valid_until'] ) {
		$valid_until = strtotime( $saved_token['valid_until'] );
		if ( time() >= $valid_until ) {
			/**
			 * Delete current token option
			 */
			delete_option( 'thrive_token_support' );

			/* delete the generated initial token */
			delete_option( 'tve_dash_generated_token' );

			/**
			 * Delete thrive user
			 */
			tve_dash_delete_support_user();
		}
	}
}
