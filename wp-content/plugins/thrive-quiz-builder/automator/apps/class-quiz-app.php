<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

namespace TQB\Automator;

use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Quiz_App extends App {
	public static function get_id() {
		return 'quiz_builder';
	}

	public static function get_name() {
		return 'Quiz Builder';
	}

	public static function get_description() {
		return 'Thrive Quiz Builder related items';
	}

	public static function get_logo() {
		return 'tap-quiz-builder-logo';
	}

	public static function has_access() {
		return true;
	}
}
