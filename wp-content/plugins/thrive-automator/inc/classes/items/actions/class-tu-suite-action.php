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


class Tu_Suite extends Tar_Suite {
	public static function get_id() {
		return 'suite_tu_action';
	}

	public static function get_name() {
		return 'Thrive Ultimatum';
	}

	public static function get_image() {
		return 'tap-ultimatum-logo';
	}
}
