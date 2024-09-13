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

class Dropdown extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'dropdown';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Dropdown', 'thrive-cb' );
	}

	public function apply( $data ) {
		$field_value    = $data['field_value'];
		$compared_value = $this->get_value();

		if ( is_numeric( $field_value ) ) {
			$compared_value = (int) $compared_value;
		}

		return $field_value === $compared_value;
	}

	public static function get_operators() {
		return [
			'equals' => [
				'label' => '=',
			],
		];
	}

	public static function get_control_type() {
		return static::get_key();
	}

	public static function is_hidden() {
		return true;
	}
}
