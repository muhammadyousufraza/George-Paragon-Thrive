<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Lesson;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Restriction_Label_Data_Field
 */
class Restriction_Label_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Restriction label';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by zero or more restriction labels';
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
		//todo check this
		foreach ( TVA_Lesson::$types as $type ) {
			$types[ $type ] = [
				'label' => $type,
				'id'    => $type,
			];
		}

		return $types;
	}

	public static function get_id() {
		return 'restriction_label';
	}

	public static function get_supported_filters() {
		return [ 'checkbox' ];
	}

	//todo check this
	public static function is_ajax_field() {
		return true;
	}

	//todo check this
	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
