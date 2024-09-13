<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Access_Type_Field
 */
class Access_Type_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Access type';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by access type';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$types = [];
		foreach ( TVA_order::get_access_types() as $key => $type ) {
			$types[ $key ] = [
				'label' => $type,
				'id'    => $key,
			];
		}

		return $types;
	}

	public static function get_id() {
		return 'access_type_slug';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'woocommerce-product';
	}
}
