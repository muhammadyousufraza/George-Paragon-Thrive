<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Automator;


use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Apprentice_App extends App {

	public static function get_id() {
		return 'apprentice';
	}

	public static function get_name() {
		return 'Apprentice';
	}

	public static function get_description() {
		return 'Thrive Apprentice';
	}

	public static function get_logo() {
		return 'tap-apprentice-logo';
	}

	public static function has_access() {
		return true;
	}
}
