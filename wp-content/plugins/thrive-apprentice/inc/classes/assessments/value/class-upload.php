<?php

namespace TVA\Assessments\Value;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Upload extends Base {
	public function prepare_value( $value ) {

		if ( $this->is_valid( $value ) ) {
			return $value;
		}

		return [];
	}

	private function is_valid( $value ) {
		return is_array( $value );
	}


	/**
	 * Injects the upload service into the user assessment
	 * Usefully to display user assessment after the admin changes the settings in the backend
	 *
	 * @return array
	 */
	public function get_value_config() {
		return [
			'service' => tva_assessment_settings()->upload_connection_key,
		];
	}
}
