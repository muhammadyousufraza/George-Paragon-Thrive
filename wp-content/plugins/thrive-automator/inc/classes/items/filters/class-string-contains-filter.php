<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class String_Contains extends Filter {

	protected $value;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'string_contains';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'String contains', 'thrive-automator' );
	}

	public function filter( $data ) {
		return Utils::string_contains_items( $data['value'], $this->value );
	}

	public static function get_operators() {
		return [
			'contains' => [
				'label' => __( 'contains', 'thrive-automator' ),
			],
		];
	}
}
