<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

use TVE\Reporting\Main;
use TVE\Reporting\Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Verify if we have the requirements
 * @return bool
 */
function thrive_reporting_dashboard_can_run() {
	return PHP_VERSION_ID >= 70000;
}

if ( thrive_reporting_dashboard_can_run() ) {
	require_once( __DIR__ . '/inc/classes/class-main.php' );

	Main::init();
}

function thrive_reporting_dashboard_register_event( $event_class ) {
	Store::get_instance()->register_event( $event_class );
}

function thrive_reporting_dashboard_register_report_app( $app_class ) {
	Store::get_instance()->register_report_app( $app_class );
}
