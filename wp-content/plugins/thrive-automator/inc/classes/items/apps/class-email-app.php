<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Email_App extends App {

	public static function get_id() {
		return 'email';
	}

	public static function get_name() {
		return 'Email';
	}

	public static function get_description() {
		return __( 'Email related items', 'thrive-automator' );
	}

	public static function get_logo() {
		return 'tap-email';
	}
}
