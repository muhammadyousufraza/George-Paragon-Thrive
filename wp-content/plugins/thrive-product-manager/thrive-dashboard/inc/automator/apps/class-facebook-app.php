<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Facebook_App extends App {

	public static function get_id() {
		return 'facebook';
	}

	public static function get_name() {
		return 'Facebook';
	}

	public static function get_description() {
		return __( 'Facebook integrations', 'thrive-dash' );
	}

	public static function get_logo() {
		return 'tap-facebook-logo';
	}

	public static function has_access() {
		return Facebook::exists();
	}

	public static function get_acccess_url() {
		return admin_url( 'admin.php?page=tve_dash_api_connect' );
	}
}
