<?php

namespace TQB\Automator;

use Thrive\Automator\Items\Data_Field;
use TQB_Quiz_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Quiz_Title_Field
 */
class Quiz_Title_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Quiz title';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Title set in Thrive Quiz Builder dashboard';
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
		$quizzes = array();
		foreach ( TQB_Quiz_Manager::get_quizzes() as $quiz ) {
			if ( false === $quiz->validation['valid'] ) {
				continue;
			}
			$quizzes[ $quiz->post_title ] = array(
				'label' => $quiz->post_title,
				'id'    => $quiz->post_title,
			);
		}

		return $quizzes;
	}

	public static function get_id() {
		return 'quiz_title';
	}

	public static function get_supported_filters() {
		return array( 'autocomplete' );
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
		return 'Example Quiz';
	}
}
