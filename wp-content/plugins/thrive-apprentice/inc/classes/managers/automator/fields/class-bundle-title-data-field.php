<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Bundle;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Bundle_Title_Field
 */
class Bundle_Title_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Bundle title';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by bundle title';
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
				'id'    => $bundle->name,
			];
		}

		return $bundles;
	}

	public static function get_id() {
		return 'bundle_title';
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
		return 'An Example Bundle';
	}
}
