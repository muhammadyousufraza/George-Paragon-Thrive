<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Level;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Difficulty_Field
 */
class Course_Difficulty_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Course difficulty';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by course difficulty';
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
		$difficulties = [];
		foreach ( TVA_Level::get_items() as $difficulty ) {
			$difficulties[ $difficulty->id ] = [
				'label' => $difficulty->name,
				'id'    => $difficulty->id,
			];
		}

		return $difficulties;
	}

	public static function get_id() {
		return 'course_difficulty';
	}

	public static function get_supported_filters() {
		return [ 'checkbox' ];
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
		return 'Easy';
	}
}
