<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Certificate_Downloaded extends Trigger {
	public static function get_id() {
		return 'thrive/certificate_downloaded';
	}

	public static function get_wp_hook() {
		return 'tva_certificate_downloaded';
	}

	public static function get_provided_data_objects() {
		return [ 'certificate_data', 'user_data', 'course_data' ];
	}

	public static function get_hook_params_number() {
		return 3;
	}

	/**
	 * Get the name of the app to which the hook belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	public static function get_name() {
		return 'Certificate was downloaded';
	}

	public static function get_description() {
		return 'Fires when a certificate is downloaded';
	}

	public static function get_image() {
		return 'tap-apprentice-logo';
	}
}
