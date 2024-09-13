<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */
namespace TVO\Automator;
use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Ovation_App extends App{

	public static function get_id() {
		return 'ovation';
	}

	public static function get_name() {
		return 'Ovation';
	}

	public static function get_description() {
		return 'Collect, manage and display conversion boosting testimonials.';
	}

	public static function get_logo() {
		return 'tap-ovation-logo';
	}

	public static function has_access() {
		return true;
	}
}

