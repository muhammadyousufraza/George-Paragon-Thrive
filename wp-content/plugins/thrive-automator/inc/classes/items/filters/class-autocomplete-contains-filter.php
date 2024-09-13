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
if ( ! class_exists( 'Autocomplete', false ) ) {
	require_once __DIR__ . '/class-autocomplete-filter.php';
}

class Autocomplete_Contains extends Autocomplete {
	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'autocomplete_contains';
	}

	public static function get_name() {
		return __( 'Autocomplete contains', 'thrive-automator' );
	}

	public function filter( $data ) {
		$result       = false;
		$filter_value = $this->value;

		while ( ! $result && count( $filter_value ) ) {
			$result = strpos( $data['value'], array_shift( $filter_value ) ) !== false;
		}

		return $result;
	}
}
