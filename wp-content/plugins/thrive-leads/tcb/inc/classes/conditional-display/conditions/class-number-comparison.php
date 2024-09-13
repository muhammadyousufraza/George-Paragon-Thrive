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

class Number_Comparison extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'number_comparison';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Number comparison', 'thrive-cb' );
	}

	public function apply( $data ) {
		$field_number    = (float) $data['field_value'];
		$compared_number = (float) $this->get_value();

		switch ( $this->get_operator() ) {
			case 'less_than':
				$result = $field_number < $compared_number;
				break;
			case 'more_than':
				$result = $field_number > $compared_number;
				break;
			case 'equal':
				$result = $field_number == $compared_number;
				break;
			default:
				$result = false;
		}

		return $result;
	}

	public static function get_operators() {
		return [
			'less_than' => [
				'label' => 'is less than',
			],
			'more_than' => [
				'label' => 'is more than',
			],
			'equal'     => [
				'label' => 'is equal to',
			],
		];
	}

	public static function get_control_type() {
		return 'input-number';
	}

	public static function get_display_size() {
		return 'medium';
	}
}
