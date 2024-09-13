<?php
/**
 * Plugin Name:  Thrive Optimize
 * Plugin URI:   https://thrivethemes.com/
 * Description:  Boost Conversion Rates by testing two or more variations of a page
 * Version: 2.28
 * Author:       Thrive Themes
 * Author URI:   https://thrivethemes.com/
 * Text Domain:  thrive-optimize
 * Domain Path:  /languages
 */

/**
 * Supported Cache Plugins:
 * - WP Super Cache: the user should clear the cache for the page each time he starts a test on
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'THRIVE_AB_PLUGIN_FILE' ) ) {
	define( 'THRIVE_AB_PLUGIN_FILE', __FILE__ );
}

// Include the main WooCommerce class.
if ( ! class_exists( 'ThriveAB' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-thrive-ab.php';
}

/**
 * Main instance of TAB.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 * @since  2.1
 * @return Thrive_AB
 */
function thrive_ab() {
	return Thrive_AB::instance();
}

thrive_ab();
