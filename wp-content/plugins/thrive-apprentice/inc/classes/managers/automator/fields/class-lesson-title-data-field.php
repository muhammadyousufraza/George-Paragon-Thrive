<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Const;
use TVA_Course_V2;
use TVA_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Lesson_Title_Field
 */
class Lesson_Title_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Lesson title';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by lesson title';
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
		$data    = [];
		$courses = TVA_Course_V2::get_items( [ 'status' => 'publish' ] );
		foreach ( $courses as $course ) {
			$course_id = $course->get_id();

			$modules = TVA_Manager::get_course_modules( $course->get_wp_term() );
			if ( empty( $modules ) ) {
				$lessons = $course->get_all_lessons();
				foreach ( $lessons as $lesson ) {
					$data[ $course_id ]['items'][ $lesson->ID ] = [
						'label' => $lesson->post_title,
						'value' => $lesson->post_title,
					];
				}
			} else {
				foreach ( $modules as $module ) {
					$lessons                                    = TVA_Manager::get_all_module_items( $module, [
						'post_type' => TVA_Const::LESSON_POST_TYPE,
					] );
					$data[ $course_id ]['items'][ $module->ID ] = [
						'label' => $module->post_title,
						'items' => [],
					];
					if ( ! empty( $lessons ) ) {
						foreach ( $lessons as $lesson ) {
							$data[ $course_id ]['items'][ $module->ID ]['items'][ $lesson->ID ] = [
								'label' => $lesson->post_title,
								'value' => $lesson->post_title,
							];
						}
					}
				}
			}
			if ( ! empty( $data[ $course_id ]['items'] ) ) {
				$data[ $course_id ]['label'] = $course->name;
			}
		}

		return $data;
	}


	public static function get_id() {
		return 'lesson_title';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete_toggle' ];
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
		return 'Example Lesson';
	}
}
