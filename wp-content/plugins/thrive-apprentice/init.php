<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once __DIR__ . '/vendor/autoload.php';

global $tva_checkout;
$tva_checkout = new TVA_Checkout();
new TVA_Thankyou();

/**
 * At this point we need to either hook into an existing Content Builder plugin or use the copy we store in the tcb folder
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// Fix for PHP 5.2<=
if ( ! defined( 'JSON_OBJECT_AS_ARRAY' ) ) {
	define( 'JSON_OBJECT_AS_ARRAY', 1 );
}

$tcb_file_exists   = file_exists( dirname( __DIR__ ) . '/thrive-visual-editor/thrive-visual-editor.php' );
$tcb_plugin_active = is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );
if ( false === $tcb_file_exists || false === $tcb_plugin_active ) {
	require_once __DIR__ . '/tcb-bridge/init.php';
}
