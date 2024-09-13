<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Schedule;

class Base implements \JsonSerializable {
	use Utils;

	public function __construct( $data = [] ) {
		$this->init_defaults();
		$this->set_data( $data );
	}

	public function set_data( $data, $value = null ) {
		if ( ! is_array( $data ) ) {
			$data = [ $data => $value ];
		}
		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Init default values for this instance
	 */
	protected function init_defaults() {

	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return get_object_vars( $this );
	}

	/**
	 * Get default values for this type of schedule
	 *
	 * @return array
	 */
	public static function get_defaults() {
		$instance = new static();

		return $instance->jsonSerialize();
	}

	/**
	 * Factory over drip schedules
	 * Used in triggers to instantiate a schedule
	 *
	 * @param array  $params
	 * @param string $type
	 *
	 * @return Base|mixed
	 */
	public static function factory( $params = [], $type = '' ) {
		$class_name = __NAMESPACE__ . '\Base';

		if ( empty( $type ) && ! empty( $params['type'] ) ) {
			$type = $params['type'];
		}

		switch ( $type ) {
			case 'repeating':
				$class_name = __NAMESPACE__ . '\Repeating';
				break;
			case 'non_repeating':
				$class_name = __NAMESPACE__ . '\Non_Repeating';
				break;
			case 'specific':
				$class_name = __NAMESPACE__ . '\Specific';
				break;
			default:
				break;
		}


		return new $class_name( $params );
	}
}
