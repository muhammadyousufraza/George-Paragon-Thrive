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

abstract class Condition {
	use Item;

	private $value;
	private $operator;
	private $um;
	private $extra;

	public function __construct( $data ) {
		$this->prepare_data( $data );
	}

	public function prepare_data( $data ) {
		$this->value = empty( $data['value'] ) ? '' : $data['value'];

		if ( ! empty( $data['operator'] ) ) {
			$this->operator = $data['operator'];
		}

		if ( ! empty( $data['extra'] ) ) {
			$this->extra = $data['extra'];
		}

		if ( ! empty( $data['um'] ) ) {
			$this->um = $data['um'];
		}
	}

	public function get_value() {
		return $this->value;
	}

	public function get_operator() {
		return $this->operator;
	}

	public function get_um() {
		return $this->um;
	}

	public function get_extra() {
		return $this->extra;
	}

	/**
	 * @return string
	 */
	abstract public static function get_key();

	abstract public static function get_label();

	abstract public function apply( $data );

	/**
	 * Function that should be implemented individually - for situations where the filter has multiple operators, get a list of them
	 */
	abstract public static function get_operators();

	/**
	 * Allows adding extra operators, for conditions on multiple rows
	 *
	 * @return array
	 */
	public static function get_extra_operators() {
		return [];
	}

	public static function get_control_type() {
		return 'input';
	}

	/**
	 * This determines whether the select for  the operator is shown or not.
	 *
	 * @return bool
	 */
	public static function is_hidden() {
		return false;
	}

	public static function get_display_size() {
		return 'small';
	}

	/**
	 * @return array
	 */
	public static function get_data_to_localize() {
		$operators = static::get_operators();

		return [
			'label'                  => static::get_label(),
			'operators'              => $operators,
			'extra_operators'        => static::get_extra_operators(),
			'is_hidden'              => static::is_hidden(),
			'control_type'           => static::get_control_type(),
			'display_size'           => static::get_display_size(),
			'validation_data'        => static::get_validation_data(),
			'is_single_operator'     => count( $operators ) === 1,
			'has_options_to_preload' => static::has_options_to_preload(),
		];
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return [
			'min' => 0,
			'max' => PHP_INT_MAX,
		];
	}

	public static function has_options_to_preload() {
		return false;
	}
}
