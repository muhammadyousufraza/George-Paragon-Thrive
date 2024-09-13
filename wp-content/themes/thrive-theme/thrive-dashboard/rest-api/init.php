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
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 23-Jul-19
 * Time: 01:28 PM
 *
 * Initializes the REST Controllers under /wp-json/td
 * - which are public and used atm by Zapier
 */

require TVE_DASH_PATH . '/rest-api/class-td-rest-controller.php';
require TVE_DASH_PATH . '/rest-api/class-td-rest-hook-controller.php';

add_action( 'rest_api_init', 'tve_dash_init_rest_controllers' );

/**
 * Register routes for different built triggers [LG/CF for the moment]
 */
function tve_dash_init_rest_controllers() {

	$rest_controller = new TD_REST_Controller();
	$rest_controller->register_routes();

	// Register LG routes
	$zapier_subscribe = new TD_REST_Hook_Controller( 'optin' );
	$zapier_subscribe->register_routes();

	// Register CF routes
	$zapier_subscribe = new TD_REST_Hook_Controller( 'cf-optin' );
	$zapier_subscribe->register_routes();
}

function tve_dash_generate_api_key() {

	$key = implode( '-', str_split( substr( strtolower( md5( microtime() . rand( 1000, 9999 ) ) ), 0, 30 ), 6 ) );

	return $key;
}
