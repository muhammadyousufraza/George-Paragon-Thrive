<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Assessment_Type_Data_Field
 */
class Assessment_Type_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Assessment Type';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Target an assessment by type';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'assessment_type';
	}

	public static function get_supported_filters() {
		return [ 'dropdown' ];
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$types = [];

		foreach ( \TVA_Assessment::$types as $type => $label ) {
			$types[ $type ] = [
				'id'    => $type,
				'label' => $label,
			];
		}

		return $types;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return '9';
	}
}
