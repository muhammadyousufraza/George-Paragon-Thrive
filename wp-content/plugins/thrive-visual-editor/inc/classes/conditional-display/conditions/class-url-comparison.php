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

class URL_Comparison extends Condition {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'url_comparison';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'URL comparison', 'thrive-cb' );
	}

	public function apply( $data ) {
		$field_value    = $data['field_value'];
		$compared_value = $this->get_value();

		if ( strpos( $compared_value, '*' ) === false ) {
			$result = strcmp( $field_value, $compared_value ) === 0;
		} else {
			/* if a wildcard '*' is found, convert it into the regex format then run a preg_match */
			$compared_value = str_replace( '\*', '.*', preg_quote( $compared_value, '/' ) );

			preg_match_all( '/' . $compared_value . '/m', $field_value, $matches, PREG_SET_ORDER );
			$result = ! empty( $matches );
		}

		return $result;
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
