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

class String_Comparison extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'string_comparison';
	}

	public static function get_control_type() {
		return 'string-comparison';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'String comparison', 'thrive-cb' );
	}

	public function apply( $data ) {
		$string_data = $this->get_value();

		$string_name     = $string_data['string_name'];
		$string_operator = $string_data['string_operator'];
		$string_value    = $string_data['string_value'];

		$field_string = isset( $data['field_value'] ) && isset( $data['field_value'][ $string_name ] ) ? sanitize_text_field( urldecode( $data['field_value'][ $string_name ] ) ) : '';

		switch ( $string_operator ) {
			case 'equals':
				$result = $field_string === $string_value;
				break;
			case 'contains':
				$result = strpos( $field_string, $string_value ) !== false;
				break;
			case 'not_contains':
				$result = strpos( $field_string, $string_value ) === false;
				break;
			case 'exists':
				$result = ! empty( $field_string );
				break;
			default:
				$result = false;
		}

		return $result;
	}

	public static function get_operators() {
		/* The operators are added dynamically, but we keep an array with one element so this will be considered a singular condition */
		return [
			[
				'value' => 'equals',
				'label' => 'Equals',
			],
		];
	}

	public static function get_extra_operators() {
		return [
			[
				'value' => 'equals',
				'label' => __( 'Equals' ),
			],
			[
				'value' => 'contains',
				'label' => __( 'Contains' ),
			],
			[
				'value' => 'not_contains',
				'label' => __( 'Does not Contain' ),
			],
			[
				'value' => 'exists',
				'label' => __( 'Exists' ),
			],
		];
	}

	public static function get_display_size() {
		return 'medium';
	}

	public static function is_hidden() {
		return true;
	}
}
