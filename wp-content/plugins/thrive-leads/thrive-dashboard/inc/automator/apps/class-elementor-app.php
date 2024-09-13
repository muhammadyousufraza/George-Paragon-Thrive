<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Elementor_App extends App {

	public static function get_id() {
		return 'elementor';
	}

	public static function get_name() {
		return 'Elementor';
	}

	public static function get_description() {
		return __( 'Elementor integrations', 'thrive-dash' );
	}

	public static function get_logo() {
		return 'tap-elementor-logo';
	}

	public static function has_access() {
		return true;
	}

	public static function hidden() {
		return ! Elementor::exists();
	}
}
