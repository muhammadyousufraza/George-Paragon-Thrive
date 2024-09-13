<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


require __DIR__ . '/class-updater.php';

use \TD\DB_Updater\Updater;

/**
 * Displays a "db upgrade required" page
 *
 * @param Updater $update_handler
 */
function tve_dash_updater_page( $update_handler ) {
	if ( ! $update_handler instanceof Updater ) {
		wp_die( 'Handler must be an instance of Updater' );
	}

	include TVE_DASH_PATH . '/templates/db-updater/splash.phtml';
	wp_enqueue_script( 'tve-dash-updater', '' );
}

/**
 * Get updater md5 key
 *
 * @param $instance
 *
 * @return string
 */
function tve_dash_get_updater_key( $instance ) {
	return md5( get_class( $instance ) );
}

add_action( 'wp_ajax_tve_dash_db_updater', static function () {
	if ( empty( $_REQUEST['key'] ) ) {
		wp_die();
	}

	/** @var Updater $instance */
	$instance = apply_filters( 'tve_dash_updater_instance_' . sanitize_title( $_REQUEST['key'] ), null );
	if ( ! $instance instanceof Updater ) {
		wp_die();
	}

	$instance->ajax_handler();
} );
