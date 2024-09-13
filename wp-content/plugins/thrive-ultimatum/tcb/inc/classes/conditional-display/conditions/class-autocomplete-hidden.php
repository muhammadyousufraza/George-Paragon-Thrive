<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Autocomplete', false ) ) {
	require_once __DIR__ . '/class-autocomplete.php';
}

/**
 * This is used by Product_Access in Apprentice
 * Because the field name there is called 'Has access to Apprentice Product(s)', it doesn't make sense to have the condition visible, so we hide it.
 */
class Autocomplete_Hidden extends Autocomplete {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'autocomplete_hidden';
	}

	public function apply( $data ) {
		$field_values = $data['field_value'];
		$haystack     = $this->get_value();

		if ( is_array( $field_values ) ) {
			$result = ! empty( array_intersect( $field_values, $haystack ) );
		} else {
			$result = in_array( $field_values, $haystack, true );
		}

		return $result;
	}

	public static function get_operators() {
		return [
			'autocomplete' => [
				'label' => 'is',
			],
		];
	}

	public static function is_hidden() {
		return true;
	}

	public static function get_control_type() {
		return Autocomplete::get_key(); /* use the 'autocomplete' control because the functionality is the same */
	}
}
