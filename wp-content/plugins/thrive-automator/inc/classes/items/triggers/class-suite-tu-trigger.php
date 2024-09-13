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
if ( ! class_exists( 'Suite_Tar', false ) ) {
	require_once __DIR__ . '/class-suite-tar-trigger.php';
}

class Suite_Tu extends Suite_Tar {

	public static function get_id() {
		return 'suite_tu_trigger';
	}

	public static function get_name() {
		return 'Thrive Ultimatum';
	}

	public static function get_image() {
		return 'tap-ultimatum-logo';
	}

}
