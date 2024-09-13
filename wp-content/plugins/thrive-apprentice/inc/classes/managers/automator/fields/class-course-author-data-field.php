<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Course_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Author_Field
 */
class Course_Author_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Course author';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by course author';
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
		$authors = [];

		foreach ( TVA_Course_V2::get_items( [ 'status' => 'publish' ] ) as $course ) {
			$author = $course->get_author();
			if ( ! empty( $author ) && $author_data = $author->get_user() ) {
				$authors[ $author_data->display_name ] = [
					'label' => $author_data->display_name,
					'id'    => $author_data->display_name,
				];
			}

		}

		return $authors;
	}

	public static function get_id() {
		return 'author_name';
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
		return 'Joe Bloggs';
	}
}
