<?php

namespace TVA\Reporting\ReportApps;

use TVA\Access\History_Table;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVA\Reporting\Utils;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Logs;
use TVE\Reporting\Report_Type;

class Top_Members extends Report_Type {
	public static function key(): string {
		return 'top_members';
	}

	public static function label(): string {
		return __( 'Top members', 'thrive-apprentice' );
	}

	/**
	 * Return an array of all filters supported by fields
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		$filters = [];

		foreach ( [ Created::class, Course_Id::class ] as $field_class ) {
			/** @var $field_class Event_Field */
			$filters[ $field_class::key() ] = [
				'label' => $field_class::get_label(),
				'type'  => $field_class::get_filter_type(),
			];
		}

		return $filters;
	}

	public static function get_data( $query = [] ): array {
		return [];
	}

	/**
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_table_data( $query ): array {
		$items       = [];
		$user_labels = [];

		$completed_lesson_count_map = Utils::get_completed_lesson_count( $query['filters'], [ Member_Id::key(), Course_Id::key() ] );
		$lesson_count_by_course_map = Utils::get_lesson_count_by_course( $query );
		$course_count               = count( $lesson_count_by_course_map );

		$students_enrollments = History_Table::get_instance()->get_top_students( $query['filters'] );

		foreach ( $completed_lesson_count_map as $member_id => $completed_lessons ) {
			$completion_rate = 0;

			/**
			 * C1 - L1, L2, L3
			 * C2 - L4, L5
			 * The user U1 completed L1, L2, L4
			 * Completion Rate = (2/3 + 1/2) / 2 = 0.58 --> 58%, where:
			 * 2/3 = number of completed lessons in C1 / total number of lessons in C1
			 * 1/2 = number of completed lessons in C2 / total number of lessons in C2
			 * 2   = total number of courses
			 */
			foreach ( $lesson_count_by_course_map as $course_id => $lesson_count ) {
				$completed_lessons = empty( $completed_lesson_count_map[ $member_id ][ $course_id ] ) ? 0 : $completed_lesson_count_map[ $member_id ][ $course_id ];
				$completion_rate   += $completed_lessons / $lesson_count;
			}
			$items[] = [
				User_Id::key()      => $member_id,
				'enrollments'       => empty( $students_enrollments[ $member_id ] ) ? 0 : $students_enrollments[ $member_id ],
				'completed_lessons' => isset( $completed_lesson_count_map[ $member_id ] ) ? array_sum( $completed_lesson_count_map[ $member_id ] ) : 0,
				'total_lessons'     => $lesson_count_by_course_map,
				'completion_rate'   => round( ( $completion_rate / $course_count ) * 100 ),
			];

			$user_labels[ $member_id ] = ( new User_Id( $member_id ) )->get_title();
		}

		$number_of_items = count( $items );

		if ( empty( $query['order_by'] ) ) {
			$query['order_by'] = 'completion_rate';
		}

		$labels = array_merge( Popular_Courses::get_default_labels(), [ User_Id::key() => User_Id::get_label_structure( $user_labels ) ] );

		$items = static::order_items( $items, $query, $labels );
		$items = static::slice_items( $items, $query );

		return [
			'labels'          => $labels,
			'items'           => $items,
			'number_of_items' => $number_of_items,
			'links'           => [
				User_Id::key() => '#members/{id}/courses',
			],
			'images'          => static::get_custom_data_images( $items, [ User_Id::class ] ),
		];
	}

	/**
	 * @param $user_ids
	 * @param $event_type
	 *
	 * @return mixed
	 */
	public static function get_lesson_count( $user_ids, $event_type ) {
		$lesson_count_map = [];

		$items = Logs::get_instance()->set_query( [
			'event_type' => $event_type,
			'filters'    => [
				User_Id::key() => $user_ids,
			],
			'group_by'   => User_Id::key(),
		] )->get_results();

		foreach ( $items as $item ) {
			$lesson_count_map[ $item[ User_Id::key() ] ] = $item['count'];
		}

		return $lesson_count_map;
	}
}
