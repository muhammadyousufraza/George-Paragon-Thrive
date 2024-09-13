<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Reset
 */
class Thrive_Reset {

	/**
	 * Add admin page for resetting settings and set the ajax action for this
	 */
	public static function init() {

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}
		add_submenu_page( '', null, null, 'manage_options', 'tap-reset', [ __CLASS__, 'menu_page' ] );

	}

	/**
	 * Admin menu page for the reset
	 */
	public static function menu_page() {
		Utils::tap_template( 'reset-page' );
	}

	/**
	 * Remove everything
	 */
	public static function factory_reset() {
		$automations = Items\Automations::get_raw_data();

		foreach ( $automations as $automation ) {
			Items\Automation::delete( $automation['id'] );
		}

		return true;
	}
}
