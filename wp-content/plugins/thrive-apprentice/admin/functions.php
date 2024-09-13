<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-university
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Display a label/status for current TA pages
 *
 * @param $states array
 * @param $post   WP_Post
 *
 * @return mixed
 */
function tva_display_post_states( $states, $post ) {

	if ( tva_get_settings_manager()->is_checkout_page( $post ) ) {
		$states['tva_checkout'] = __( 'Thrive Apprentice SendOwl Checkout', 'thrive-apprentice' );
	}

	if ( tva_get_settings_manager()->is_thankyou_page( $post->ID ) ) {
		$states['tva_thank_you'] = __( 'Thrive Apprentice SendOwl Thank You', 'thrive-apprentice' );
	}

	if ( tva_get_settings_manager()->is_index_page( $post ) ) {
		$states['tva_index'] = __( 'Thrive Apprentice Courses', 'thrive-apprentice' );
	}

	if ( tva_get_settings_manager()->is_register_page( $post ) ) {
		$states['tva_register'] = __( 'Thrive Apprentice Register', 'thrive-apprentice' );
	}

	if ( tva_get_settings_manager()->is_login_page( $post ) ) {
		$states['tva_login'] = __( 'Thrive Apprentice Login', 'thrive-apprentice' );
	}

	return $states;
}
