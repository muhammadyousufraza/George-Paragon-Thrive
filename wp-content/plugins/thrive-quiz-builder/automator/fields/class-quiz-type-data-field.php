<?php

namespace TQB\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Quiz_Type_Field
 */
class Quiz_Type_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Quiz type';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Type set in Thrive Quiz Builder dashboard';
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
		$types = array();
		foreach ( tqb()->get_quiz_types() as $type ) {
			$types[ $type['key'] ] = array(
				'label' => $type['label'],
				'id'    => $type['key'],
			);
		}

		return $types;
	}

	public static function get_id() {
		return 'quiz_type';
	}

	public static function get_supported_filters() {
		return array( 'checkbox' );
	}

	public static function is_ajax_field() {
		return true;
	}

	/**
	 * Get the type of the field value
	 *
	 * @return string
	 */
	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'Number';
	}
}
