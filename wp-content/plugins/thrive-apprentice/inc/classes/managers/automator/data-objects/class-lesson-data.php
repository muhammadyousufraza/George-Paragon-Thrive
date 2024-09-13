<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA_Lesson;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Lesson_Data
 */
class Lesson_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'lesson_data';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'lesson_id', 'lesson_title', 'course_title', 'module_title', 'lesson_type', 'author_id' ];
	}

	public static function get_nice_name() {
		return 'Apprentice lesson ID';
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Lesson_Data object' );
		}

		$lesson = null;
		if ( is_a( $param, 'TVA_Lesson' ) ) {
			$lesson = $param;
		} elseif ( is_numeric( $param ) ) {
			$lesson = new TVA_Lesson( $param );
		} elseif ( is_array( $param ) ) {
			$lesson = new TVA_Lesson( $param['lesson_id'] );
		}

		if ( $lesson ) {
			$post          = $lesson->get_the_post();
			$course        = $lesson->get_course();
			$module_parent = $lesson->get_parent_by_type( \TVA_Const::MODULE_POST_TYPE );

			return [
				'lesson_id'    => $post->ID,
				'lesson_title' => $post->post_title,
				'lesson_type'  => $lesson->get_type(),
				'module_id'    => $post->post_parent,
				'module_title' => empty( $module_parent ) ? '' : $module_parent->post_title,
				'course_id'    => $course->get_id(),
				'course_title' => $course->name,
				'author_id'    => $post->post_author,
			];
		}

		return $lesson;
	}

	public static function get_data_object_options() {
		return Main::get_lessons();
	}
}
