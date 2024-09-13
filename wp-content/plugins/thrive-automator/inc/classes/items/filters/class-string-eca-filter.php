<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


if ( ! class_exists( 'String_Ec', false ) ) {
	require_once __DIR__ . '/class-string-ec-filter.php';
}

class String_Eca extends String_Ec {

	protected $value;

	protected $operation;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'string_eca';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'String equals, exists and autocomplete', 'thrive-automator' );
	}

	public function filter( $data ) {
		switch ( $this->operation ) {

			case 'contains':
				$result = Utils::string_contains_items( $data['value'], $this->value );
				break;
			case 'empty':
				$result = empty( $data['value'] );
				break;

			case 'not_empty':
				$result = ! empty( $data['value'] );
				break;
			case 'autocomplete':
				if ( is_array( $data['value'] ) ) {
					$result = ! empty( array_intersect( $data['value'], $this->value ) );
				} else {
					$result = in_array( $data['value'], $this->value );
				}
				break;
			default:
				$result = false;

		}

		return $result;
	}

	public static function get_operators() {
		return [
			'autocomplete' => [
				'label' => __( 'is any of the following', 'thrive-automator' ),
			],
			'contains'     => [
				'label' => __( 'contains', 'thrive-automator' ),
			],
			'empty'        => [
				'label' => __( 'is empty', 'thrive-automator' ),
			],
			'not_empty'    => [
				'label' => __( 'is not empty', 'thrive-automator' ),
			],
		];
	}

}
