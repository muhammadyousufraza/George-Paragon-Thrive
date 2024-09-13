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

if ( ! class_exists( 'Tar_Suite', false ) ) {
	require_once __DIR__ . '/class-tar-suite-action.php';
}


class Tqb_Suite extends Tar_Suite {
	public static function get_id() {
		return 'suite_tqb_action';
	}

	public static function get_name() {
		return 'Thrive Quiz Builder';
	}

	public static function get_image() {
		return 'tap-quiz-builder-logo';
	}
}
