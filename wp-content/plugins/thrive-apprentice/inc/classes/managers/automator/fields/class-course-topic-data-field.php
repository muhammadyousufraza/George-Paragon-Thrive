<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Topic;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Topic_Field
 */
class Course_Topic_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Course topic';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by course topic';
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
		$topics = [];
		foreach ( TVA_Topic::get_items() as $topic ) {
			$topics[ $topic->id ] = [
				'label' => $topic->title,
				'id'    => $topic->id,
			];
		}

		return $topics;
	}

	public static function get_id() {
		return 'course_topic';
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
		return 'Example Topic';
	}
}
