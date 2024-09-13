<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Autocomplete', false ) ) {
	require_once __DIR__ . '/class-autocomplete-filter.php';
}

class Checkbox extends Autocomplete {

	protected $value;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'checkbox';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Checkbox', 'thrive-automator' );
	}

	public static function get_operators() {
		return [
			'checkbox' => [
				'label' => __( 'is any of the following', 'thrive-automator' ),
			],
		];
	}
}
