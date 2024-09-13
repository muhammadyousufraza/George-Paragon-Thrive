<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Bundle;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Bundle_Id_Data_Field
 */
class Bundle_Id_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Bundle id';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by bundle id';
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
		$bundles = [];
		foreach ( TVA_Bundle::get_list() as $bundle ) {
			$bundles[ $bundle->id ] = [
				'label' => $bundle->name,
				'id'    => $bundle->id,
			];
		}

		return $bundles;
	}

	public static function get_id() {
		return 'bundle_id';
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
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return 2;
	}
}
