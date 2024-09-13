<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Object;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Certificate_Data extends Data_Object {
	public static function get_id() {
		return 'certificate_data';
	}

	public static function get_nice_name() {
		return 'Apprentice certificate';
	}

	public static function create_object( $param ) {

		return $param;
	}

	public static function get_fields() {
		return [ 'certificate_number' ];
	}
}
