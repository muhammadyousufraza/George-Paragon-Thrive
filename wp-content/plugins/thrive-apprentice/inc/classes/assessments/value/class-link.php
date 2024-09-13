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

class Link extends Base {
	public function prepare_value( $value ) {

		if ( $this->is_valid( $value ) ) {
			return $value;
		}

		return '';
	}

	private function is_valid( $value ) {
		return filter_var( $value, FILTER_VALIDATE_URL ) !== false;
	}
}
