<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay;

use TCB\ConditionalDisplay\Traits\Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Field {
	use Item;

	/**
	 * @return string
	 */
	abstract public static function get_entity();

	abstract public function get_value( $object );

	/**
	 * @return string
	 */
	abstract public static function get_key();

	/**
	 * @return string
	 */
	abstract public static function get_label();

	/**
	 * @return array
	 */
	abstract public static function get_conditions();

	/**
	 * @param array  $selected_values
	 * @param string $search
	 *
	 * @return array
	 */
	public static function get_options( $selected_values = [], $search = '' ) {
		return [];
	}

	public static function filter_options( $id, $label, $selected_values = [], $searched_keyword = '' ) {
		return
			( empty( $selected_values ) || in_array( $id, $selected_values ) ) && /* if there are pre-selected values, only return the options for those */
			( empty( $searched_keyword ) || stripos( $label, $searched_keyword ) !== false ); /* if there is a searched keyword, only return the options that match it */
	}

	/**
	 * @return array
	 */
	public static function get_data_to_localize() {
		return [
			'label'                    => static::get_label(),
			'is_boolean'               => static::is_boolean(),
			'autocomplete_placeholder' => static::get_autocomplete_placeholder(), //todo will delete after removing the implementation from the other plugins
			'placeholder_text'         => static::get_placeholder_text(),
			'validation_data'          => static::get_validation_data(),
		];
	}

	/**
	 * Boolean fields can return the condition value directly ( is_logged_in, is_logged_out )
	 *
	 * @return bool
	 */
	public static function is_boolean() {
		return false;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return static::get_placeholder_text();
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_text() {
		return '';
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return [
			'min' => 0,
			'max' => 1000,
		];
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 100;
	}
}
