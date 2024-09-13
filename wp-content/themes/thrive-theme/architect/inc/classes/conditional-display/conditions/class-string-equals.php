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

class String_Equals extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'string_equals';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'String equals', 'thrive-cb' );
	}

	public function apply( $data ) {
		$a = $data['field_value'];
		$b = $this->get_value();

		return strcmp( $a, $b ) === 0;
	}

	public static function get_operators() {
		return [
			'equals' => [
				'label' => '=',
			],
		];
	}

	public static function is_hidden() {
		return true;
	}
}
