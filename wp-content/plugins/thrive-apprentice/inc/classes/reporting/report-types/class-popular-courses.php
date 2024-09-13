<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\ReportApps;

use TVA\Access\History_Table;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVA\Reporting\Events\Lesson_Complete;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVA\Reporting\Utils;
use TVE\Reporting\Report_Type;

class Popular_Courses extends Report_Type {
	public static function key(): string {
		return 'popular_courses';
	}

	public static function label(): string {
		return __( 'Popular courses', 'thrive-apprentice' );
	}

	/**
	 * Return an array of all filters supported by fields
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		$filters = [];

		foreach ( [ Created::class, Member_Id::class ] as $field_class ) {
			/** @var $field_class Event_Field */
			$filters[ $field_class::key() ] = [
				'label' => $field_class::get_label(),
				'type'  => $field_class::get_filter_type(),
			];
		}

		$filters['course_status'] = [
			'label' => __( 'Course status ', 'thrive-apprentice' ),
			'type'  => 'multiple-select',
		];

		return $filters;
	}

	/**
	 * Popular course are calculated based on enrolled users
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_chart_data( $query = [] ): array {
		$items      = [];
		$labels     = [];
		$course_key = Course_Id::key();

		$labels[ $course_key ] = Course_Id::get_label_structure();

		foreach ( static::get_enrolled_users_count( $query['filters'] ) as $course_id => $count ) {
			$items[] = [
				$course_key => $course_id,
				'count'     => $count,
			];

			$course = new Course_Id( $course_id );

			$labels[ $course_key ]['values'][ $course_id ] = $course->get_title();

		}

		return [
			'labels'       => $labels,
			'items'        => $items,
			'tooltip_text' => '<strong>{number}</strong> ' . __( 'enrolments', 'thrive-apprentice' ),
		];
	}

	/**
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_table_data( $query ): array {
		$course_key = Course_Id::key();
		$items      = [];

		$labels                = static::get_default_labels();
		$labels[ $course_key ] = Course_Id::get_label_structure();

		$courses = Utils::get_courses( $query );

		$completed_lesson_count_map = Utils::get_completed_lesson_count( $query['filters'], [ Course_Id::key(), Member_Id::key() ] );
		$enrolled_users_count_map   = static::get_enrolled_users_count( $query['filters'] );

		foreach ( $courses as $course ) {
			$course_id = $course->term_id;

			$completed_lessons_number = isset( $completed_lesson_count_map[ $course_id ] ) ? array_sum( $completed_lesson_count_map[ $course_id ] ) : 0;

			$members_that_completed_the_course = empty( $completed_lesson_count_map[ $course_id ] ) ? 1 : count( $completed_lesson_count_map[ $course_id ] );

			$lesson_count = $course->count_lessons();

			$items[] = [
				$course_key         => $course_id,
				'enrollments'       => empty( $enrolled_users_count_map[ $course_id ] ) ? 0 : $enrolled_users_count_map[ $course_id ],
				'completed_lessons' => $completed_lessons_number,
				'completion_rate'   => $lesson_count === 0 ? 0 : round( ( $completed_lessons_number / $members_that_completed_the_course ) * 100 / $lesson_count ),
			];

			$labels[ $course_key ]['values'][ $course_id ] = $course->name;
		}

		$number_of_items = count( $items );

		if ( empty( $query['order_by'] ) ) {
			$query['order_by'] = 'completion_rate';
		}

		$items = static::order_items( $items, $query, $labels );
		$items = static::slice_items( $items, $query );

		return [
			'labels'          => $labels,
			'items'           => $items,
			'number_of_items' => $number_of_items,
			'links'           => [
				Course_Id::key() => '#reports/courses?course_id={id}',
			],
		];
	}

	/**
	 * @param $filters
	 *
	 * @return array
	 */
	public static function get_enrolled_users_count( $filters ) {
		$enrolled_users_map = [];

		$filters['course_id'] = 'IS NOT NULL';

		foreach ( History_Table::get_instance()->get_course_enrollment_dates( $filters ) as $item ) {
			$enrolled_users_map[ $item[ Course_Id::key() ] ] = $item['count'];
		}

		return $enrolled_users_map;
	}

	/**
	 * Since these are not actual event fields, they are structured here.
	 * (!) This is also used by the Top Members report - if this changes, make sure to separate the function.
	 *
	 * @return array[]
	 */
	public static function get_default_labels() {
		return [
			'enrollments'       => [
				'key'    => 'enrollments',
				'text'   => __( 'Enrollments', 'thrive-apprentice' ),
				'values' => [],
			],
			'completed_lessons' => [
				'key'    => 'completed_lessons',
				'text'   => __( 'Lessons completed', 'thrive-apprentice' ),
				'values' => [],
			],
			'completion_rate'   => [
				'key'    => 'completion_rate',
				'text'   => __( 'Completion rate', 'thrive-apprentice' ),
				'values' => [],
				'um'     => '%',
			],
		];
	}
}
