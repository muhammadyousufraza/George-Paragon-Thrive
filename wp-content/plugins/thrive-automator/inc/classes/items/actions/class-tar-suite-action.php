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


class Tar_Suite extends Action {
	public static function get_id() {
		return 'suite_tar_action';
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

	public static function get_required_action_fields() {
		return [];
	}

	public static function get_required_data_objects() {
		return [];
	}

	public static function get_app_id() {
		return Thrive_Suite_App::get_id();
	}

	public static function hidden() {
		return Utils::has_suite_access();
	}

	public function do_action( $data ) {

	}
}
