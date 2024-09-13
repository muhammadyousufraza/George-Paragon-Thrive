<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Advanced_Mapping extends Action {
	protected $data_object;
	protected $mapped_values;

	protected $field;
	protected $field_data_object;

	public static function get_id() {
		return 'thrive/advanced_mapping';
	}

	public static function get_name() {
		return __( 'Advanced data mapping', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Map available data to a new data set and define specific value pairs. Use this to transform generic data fields such as incoming webhook or form fields into dynamic data sources that can be used by downstream actions.', 'thrive-automator' );
	}

	public static function get_image() {
		return 'tap-advanced-mapping';
	}


	public static function get_required_action_fields() {
		return [];
	}

	public static function get_required_data_objects() {
		return [];
	}

	public function prepare_data( $data = [] ) {
		$this->data_object       = $data['mapped_object']['value'];
		$this->mapped_values     = $data['mapping_fields']['value'];
		$this->field             = $data['field_id']['value'];
		$this->field_data_object = $data['field_data_object']['value'];
	}

	public static function is_top_level() {
		return true;
	}

	public function do_action( $data ) {
		global $automation_data;
		$data_sets = Data_Object::get();

		$field_data_object = empty( $data_sets[ $this->field_data_object ] ) ? 'generic_data' : $this->field_data_object;
		$data_object       = $automation_data->get( $field_data_object );
		if ( ! empty( $data_object ) ) {
			$initial_field_value = $data_object->get_value( $this->field );
			$key                 = array_search( $initial_field_value, array_column( $this->mapped_values, 'key' ) );
			if ( $key === false ) {
				$new_value = $initial_field_value;
			} else {
				$mask = $this->mapped_values[ $key ];

				//if we don't have a value for the key, we use the initial field value
				$new_value = $mask['value'] ?: $initial_field_value;
			}

			$data_object->set_value( $field_data_object, $new_value );
			$automation_data->set( $this->data_object, new $data_sets[ $this->data_object ]( $new_value ) );
		}

	}

	public static function sync_action_data( $data ) {
		if ( ! empty( $data['extra_data']['mapped_object']['value'] ) ) {
			$data['provided_params'] = [ $data['extra_data']['mapped_object']['value'] ];
		}

		return $data;
	}
}
