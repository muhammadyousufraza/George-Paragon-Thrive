<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Course_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Id_Data_Field
 */
class Course_Id_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Course id';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by course id';
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
			$courses[ $course->term_id ] = [
				'label' => $course->name,
				'id'    => $course->term_id,
			];
		}

		return $courses;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_id() {
		return 'course_id';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return 5;
	}

	public static function primary_key() {
		return Course_Data::get_id();
	}
}
