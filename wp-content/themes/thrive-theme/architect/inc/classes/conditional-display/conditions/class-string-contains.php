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

class String_Contains extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'string_contains';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'String contains', 'thrive-cb' );
	}

	public function apply( $data ) {
		$haystack = $data['field_value'];
		$needle   = $this->get_value();

		return strpos( $haystack, $needle ) !== false;
	}

	public static function get_operators() {
		return [
			'contains' => [
				'label' => '=',
			],
		];
	}

	public static function is_hidden() {
		return true;
	}
}
