<?php

namespace TQB\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Quiz_Text_Result_Field */
class Quiz_Questions_Answers_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Quiz questions and answers';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return '';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'quiz_questions_answers';
	}

	public static function get_supported_filters() {
		return array();
	}

	/**
	 * Get the type of the field value
	 *
	 * @return string
	 */
	public static function get_field_value_type() {
		return static::TYPE_ARRAY;
	}

	public static function get_dummy_value() {
		return [ 'question' => 'answer' ];
	}
}
