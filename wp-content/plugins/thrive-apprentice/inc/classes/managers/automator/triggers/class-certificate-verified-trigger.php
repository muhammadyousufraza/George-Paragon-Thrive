<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Certificate_Verified extends Trigger {
	public static function get_id() {
		return 'thrive/certificate_verified';
	}

	public static function get_wp_hook() {
		return 'tva_certificate_verified';
	}

	public static function get_provided_data_objects() {
		return [ 'certificate_data', 'user_data', 'course_data' ];
	}

	public static function get_hook_params_number() {
		return 3;
	}

	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	public static function get_name() {
		return 'Certificate was verified';
	}

	public static function get_description() {
		return 'Fires when a certificate is verified';
	}

	public static function get_image() {
		return 'tap-apprentice-logo';
	}
}