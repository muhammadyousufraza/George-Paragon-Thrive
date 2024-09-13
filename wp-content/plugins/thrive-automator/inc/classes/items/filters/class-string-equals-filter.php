<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class String_Equals extends Filter {

	protected $value;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'string_equals';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'String equals', 'thrive-automator' );
	}

	public function filter( $data ) {
		return $this->value == $data['value'];
	}

	public static function get_operators() {
		return [
			'equals' => [
				'label' => '=',
			],
		];
	}
}
