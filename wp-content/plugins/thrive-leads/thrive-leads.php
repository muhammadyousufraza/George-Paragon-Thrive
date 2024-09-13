<?php

/*
Plugin Name: Thrive Leads
Plugin URI: https://thrivethemes.com
Version: 3.28
Author: <a href="https://thrivethemes.com">Thrive Themes</a>
Description: The ultimate lead capture solution for WordPress
Text Domain: thrive-leads
*/

/* the base URL for the plugin */
define( 'TVE_LEADS_URL', str_replace( array(
	'http://',
	'https://',
), '//', plugin_dir_url( __FILE__ ) ) );

define( 'TVE_LEADS_PATH', plugin_dir_path( __FILE__ ) );
define( 'TVE_LEADS_PLUGIN__FILE__', __FILE__ );

/**
 * bootstrap everything
 */
require_once plugin_dir_path( __FILE__ ) . 'start.php';

/* admin entry point */
if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'admin/start.php';
}
