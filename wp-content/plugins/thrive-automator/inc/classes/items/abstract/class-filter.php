<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Traits\Automation_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Filter
 *
 * base class for all filters
 * basic shape of a action
 */
abstract class Filter {

	use Automation_Item;

	/**
	 * Current automation id
	 *
	 * @var int|mixed
	 */
	protected $aut_id;

	public function __construct( $data = [], $aut_id = 0 ) {
		if ( ! empty( $data ) ) {
			$this->prepare_data( $data );
		}
		$this->aut_id = $aut_id;
	}

	/**
	 * Function to implement individually, do actual filter operation
	 */
	abstract public function filter( $data );

	/**
	 * Unique identifier for the filter
	 *
	 * @return string
	 */
	abstract public static function get_id();

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	abstract public static function get_name();

	/**
	 * Function to implement individually, for situations where the filter has multiple operators, get a list of them
	 */
	abstract public static function get_operators();

	/**
	 * Just return the value of the filter
	 *
	 * @return mixed
	 */
	public function get_value_data() {
		return empty( $this->value ) ? null : $this->value;
	}

	/**
	 * Method to extend functionality for filter. prepare data, save what is needed. (value to compare to, unit of measure, operator)
	 */
	public function prepare_data( $data = [] ) {
		if ( isset( $data['value'] ) ) {
			$this->value = $data['value'];
		}
	}

	/**
	 * Get filter information
	 */
	final public static function get_info(): array {
		return [
			'id'        => static::get_id(),
			'name'      => static::get_name(),
			'operators' => static::get_operators(),
		];
	}

	/**
	 * Get filter information wrapper
	 */
	final public function localize_data(): array {
		return [
			'id'   => static::get_id(),
			'info' => static::get_info(),
		];
	}

	/**
	 * @return string[]
	 */
	final public static function required_properties(): array {
		return [
			'get_id'        => 'string',
			'get_name'      => 'string',
			'get_operators' => 'array',
		];
	}

	/**
	 * Get list of filters for a certain field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	final public static function get_field_filters( array $field ): array {
		$filters = static::get();

		$available_filters = [];

		foreach ( $field['filters'] as $field_filter ) {
			$available_filters[ $field_filter ]['info'] = $filters[ $field_filter ]::get_info();
		}

		return $available_filters;
	}

	/**
	 * Hide existing filter
	 */
	public static function hidden() {
		return false;
	}
}
