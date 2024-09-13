<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

namespace TU\Automator;

use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Ultimatum_App extends App {

	public static function get_id() {
		return "thrive_ultimatum";
	}

	public static function get_name() {
		return 'Ultimatum';
	}

	public static function get_description() {
		return 'The ultimate scarcity plugin for WordPress';
	}

	public static function get_logo() {
		return 'tap-ultimatum-logo';
	}

	public static function has_access() {
		return true;
	}
}
