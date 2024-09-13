<?php
/**
 * Plugin Name: Thrive Automator
 * Plugin URI: https://thrivethemes.com
 * Version: 10.1
 * Author: <a href="https://thrivethemes.com">Thrive Themes</a>
 * Author URI: https://thrivethemes.com
 * Description: Create smart automations that integrate your website with your favourite apps and plugins.
 * Requires PHP: 7.4
*/

use Thrive\Automator\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

defined( 'TAP_PLUGIN_URL' ) || define( 'TAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'TAP_PLUGIN_PATH' ) || define( 'TAP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
defined( 'TAP_PLUGIN_FILE_PATH' ) || define( 'TAP_PLUGIN_FILE_PATH', plugin_basename( __FILE__ ) );

require_once TAP_PLUGIN_PATH . 'inc/constants.php';


if ( thrive_automator_requirements() ) {
	require_once TAP_PLUGIN_PATH . '/inc/classes/class-admin.php';

	Admin::init();
}


/**
 * Thrive Automator requirements
 *
 * @return bool
 */
function thrive_automator_requirements() {
	$ok = true;

	$error_message = '';

	if ( PHP_VERSION_ID < 70000 ) {
		$ok            = false;
		$error_message = __( 'Thrive Automator requires PHP version 7 or higher in order to work.', TAP_DOMAIN );
	} elseif ( ! version_compare( get_bloginfo( 'version' ), TAP_REQUIRED_WP_VERSION, '>=' ) ) {
		$ok            = false;
		$error_message = __( 'Thrive Automator requires WordPress version ' . TAP_REQUIRED_WP_VERSION . ' in order to work. Please update your WordPress version.', TAP_DOMAIN );
	}

	if ( ! $ok ) {
		add_action( 'admin_notices', static function () use ( $error_message ) {
			echo sprintf( '<div class="notice notice-error error"><p>%s</p></div>', esc_html( $error_message ) );
		} );
	}

	return $ok;
}
