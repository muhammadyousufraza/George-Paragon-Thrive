<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA_Course_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Data
 */
class Course_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'course_data';
	}

	public static function get_nice_name() {
		return 'Apprentice course ID';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'course_id', 'course_title', 'course_topic', 'course_difficulty', 'course_restricted', 'author_name', 'course_progress' ];
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Course_Data object' );
		}

		$course = null;
		if ( is_a( $param, 'TVA_Course_V2' ) ) {
			$course = $param;
		} elseif ( is_numeric( $param ) ) {
			$course = new TVA_Course_V2( (int) $param );
		} elseif ( ! empty( $param['course_id'] ) && is_numeric( $param['course_id'] ) ) {
			$course = new TVA_Course_V2( (int) $param['course_id'] );
		} elseif ( is_array( $param ) ) {
			$course = new TVA_Course_V2( (int) $param[0] );
		}

		/* Compute the progress of this course */
		$total = (int) $course->get_published_lessons_count();
		if ( $total ) {
			$learned_lessons = (int) tva_count_completed_items( $course->get_published_lessons() );
			$progress        = (int) ( $learned_lessons * 100 / $total );
		} else {
			$progress = 0;
		}

		if ( $course ) {
			return [
				'course_id'         => $course->get_id(),
				'course_title'      => $course->name,
				'author_name'       => $course->get_author()->get_user()->display_name,
				'course_topic'      => $course->get_topic()->title,
				'course_difficulty' => $course->get_difficulty()->id,
				'course_restricted' => $course->is_private(),
				'course_progress'   => $progress,
			];
		}

		return $course;
	}

	public static function get_data_object_options() {
		$options = [];
		foreach ( TVA_Course_V2::get_items( [ 'status' => 'publish' ] ) as $course ) {
			$options[ $course->term_id ] = [
				'label' => $course->name,
				'id'    => $course->term_id,
			];
		}

		return $options;
	}
}
