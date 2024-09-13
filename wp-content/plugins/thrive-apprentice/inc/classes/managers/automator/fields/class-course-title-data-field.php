<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Course_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Title_Field
 */
class Course_Title_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Course title';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by course title';
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
		$courses = [];

		foreach ( TVA_Course_V2::get_items( [ 'status' => 'publish' ] ) as $course ) {
			$courses[ $course->name ] = [
				'label' => $course->name,
				'id'    => $course->name,
			];
		}

		return $courses;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_id() {
		return 'course_title';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'An Example Course';
	}
}
