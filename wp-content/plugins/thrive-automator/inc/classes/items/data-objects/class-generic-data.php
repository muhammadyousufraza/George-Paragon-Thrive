<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Generic_Data
 */
class Generic_Data extends Data_Object {

	protected $data = [];

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'generic_data';
	}

	public static function get_nice_name() {
		return __( 'Generic data', 'thrive-automator' );
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [];
	}

	public static function create_object( $param ) {

		return [];
	}

	public function add_field( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Keep a mapping between the field key and the field object
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function get_value( $key ) {
		return empty( $this->data[ $key ]['value'] ) ? null : $this->data[ $key ]['value'];
	}

	public function replace_dynamic_data( $value ) {
		$data = [];
		foreach ( $this->data as $item ) {
			if ( ! empty( $item['id'] ) ) {
				$data[ $item['id'] ] = $item['value'];
			}
		}

		return Utils::replace_additional_data_shortcodes( $value, $data );
	}
}
