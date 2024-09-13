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

class Youtube extends Base {
	public function prepare_value( $value ) {

		if ( $this->is_valid( $value ) ) {
			return $value;
		}

		return '';
	}

	private function is_valid( $value ) {
		$rx = '~^(?:https?://)?(?:www[.])?(?:youtube[.]com/watch[?]v=|youtu[.]be/)([^&]{11})~x';

		preg_match( $rx, $value, $matches );

		return is_array( $matches ) && ! empty( $matches[1] );
	}
}
