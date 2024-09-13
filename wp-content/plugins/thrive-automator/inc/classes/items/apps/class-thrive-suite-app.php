<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Suite_App extends App {
	public static function get_id() {
		return 'thrive_suite';
	}

	public static function get_name() {
		return __( 'Thrive Suite', 'thrive-automator' );
	}

	public static function get_description() {
		return '';
	}

	public static function get_logo() {
		return 'tap-thrive-suite';
	}

	public static function hidden() {
		return Utils::has_suite_access();
	}

	/**
	 * Suite is enabled atm if there is a plugin/theme with TAr enabled
	 *
	 * @return bool
	 */
	public static function has_access() {
		return Utils::has_suite_access();
	}

	public static function get_acccess_url() {
		return 'https://thrivethemes.com/suite';
	}
}
