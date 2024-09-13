<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Data
 */
class Global_Data extends Data_Object {

	/**
	 * Create the global data object, usually empty
	 *
	 * @param array $data
	 * @param int   $aut_id
	 */
	public function __construct( $data = [], $aut_id = 0 ) {
		$this->data   = $data;
		$this->aut_id = $aut_id;
	}

	public static function get_id() {
		return TAP_GLOBAL_DATA_OBJECT;
	}

	public static function get_nice_name() {
		return __( 'Global data', 'thrive-automator' );
	}

	/**
	 * Data fields that are used in the global data
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			Global_Date_Field::get_id(),
			Global_Date_Day_Field::get_id(),
			Global_Date_Month_Field::get_id(),
			Global_Date_Year_Field::get_id(),
			Global_Date_Time_Field::get_id(),
		];
	}

	public static function create_object( $param ) {
		return [];
	}

	/**
	 * Call value callback only on request, don't create the object when a trigger is called.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function get_value( $field = '' ) {

		if ( empty( $field ) ) {
			return null;
		}

		if ( is_subclass_of( $field, Data_Field::class ) ) {
			$field_id = $field::get_id();
		} else {
			$field_id = $field;

			$data_fields = Data_Field::get();

			if ( isset( $data_fields[ $field_id ] ) ) {
				$field = $data_fields[ $field_id ];
			} else {
				return null;
			}
		}

		if ( ! isset( $this->data[ $field_id ] ) ) {
			if ( method_exists( $field, 'value_callback' ) ) {
				$this->data[ $field_id ] = $field::value_callback();
			} else {
				$this->data[ $field_id ] = null;
			}
		}

		return $this->data[ $field_id ];
	}
}
