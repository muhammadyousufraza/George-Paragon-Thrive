<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Conditions;

use TCB\ConditionalDisplay\Condition;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Autocomplete extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'autocomplete';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Autocomplete', 'thrive-cb' );
	}

	public static function get_display_size() {
		return 'full';
	}

	public function apply( $data ) {
		$result          = false;
		$compared_values = $this->get_value();
		if ( ! empty( $compared_values ) ) {
			$field_values = $data['field_value'];
			if ( is_array( $field_values ) ) {
				$result = ! empty( array_intersect( $field_values, $compared_values ) );
			} else {
				$result = in_array( $field_values, $compared_values );
			}
		}

		return $this->get_operator() === 'autocomplete' ? $result : ! $result;
	}

	public static function get_operators() {
		return [
			'autocomplete'         => [
				'label' => 'is',
			],
			'autocomplete_exclude' => [
				'label' => 'is not',
			],
		];
	}

	public static function get_control_type() {
		return static::get_key();
	}

}
