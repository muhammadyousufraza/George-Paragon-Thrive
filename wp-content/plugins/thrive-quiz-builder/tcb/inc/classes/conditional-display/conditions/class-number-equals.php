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

class Number_Equals extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'number_equals';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Number equals', 'thrive-cb' );
	}

	public function apply( $data ) {
		$a = (int) $data['field_value'];
		$b = (int) $this->get_value();

		return $a === $b;
	}

	public static function get_operators() {
		return [
			'equals' => [
				'label' => '=',
			],
		];
	}

	public static function get_control_type() {
		return 'input-number';
	}

	public static function is_hidden() {
		return true;
	}
}
