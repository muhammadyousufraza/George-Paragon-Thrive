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

class Checkbox extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'checkbox';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Checkbox', 'thrive-cb' );
	}

	public function apply( $data ) {

		$field_values = $data['field_value'];
		$haystack     = $this->get_value();
		if ( is_array( $field_values ) ) {
			$result = ! empty( array_intersect( $field_values, $haystack ) );
		} else {
			$result = in_array( $field_values, $haystack );
		}

		return $this->get_operator() === 'contains' ? $result : ! $result;
	}

	public static function get_operators() {
		return [
			'contains'     => [
				'label' => esc_html__( 'is', 'thrive-cb' ),
			],
			'not_contains' => [
				'label' => esc_html__( 'is not', 'thrive-cb' ),
			],
		];
	}

	public static function get_control_type() {
		return static::get_key();
	}

	public static function get_display_size() {
		return 'full';
	}

	public static function has_options_to_preload() {
		return true;
	}
}
