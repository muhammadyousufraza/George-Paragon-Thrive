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

class Suite_Tar extends Trigger {

	public static function get_id() {
		return 'suite_tar_trigger';
	}

	public static function get_wp_hook() {
		return '';
	}

	public static function get_provided_data_objects() {
		return [];
	}

	public static function get_hook_params_number() {
		return 0;
	}

	public static function get_name() {
		return 'Thrive Architect';
	}

	public static function get_description() {
		return '';
	}

	public static function get_image() {
		return 'tap-architect-logo';
	}

	public static function hidden() {
		return Utils::has_suite_access();
	}

	public static function get_app_id() {
		return Thrive_Suite_App::get_id();
	}
}
