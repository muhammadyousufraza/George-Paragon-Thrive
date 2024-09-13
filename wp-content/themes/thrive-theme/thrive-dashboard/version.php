<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * TD version file. Returns the version of TD when included
 **
 */

if ( ! function_exists( 'tve_dash_load' ) ) {

	add_action( 'after_setup_theme', 'tve_dash_load', 9 );

	function tve_dash_version_compare( $v1, $v2 ) {
		return version_compare( $v1, $v2 );
	}

	/**
	 * Load test
	 */
	function tve_dash_load() {
		uksort( $GLOBALS['tve_dash_versions'], 'tve_dash_version_compare' );

		$last_dash = $GLOBALS['tve_dash_included'] = end( $GLOBALS['tve_dash_versions'] );

		$GLOBALS['tve_dash_loaded_from'] = $last_dash['from'];

		require_once $last_dash['path'];
	}
}

return '3.43';
