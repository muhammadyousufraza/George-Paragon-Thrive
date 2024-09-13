<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Lesson_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVA\Reporting\Events\Lesson_Complete;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Report_Type;

class Course_Progress extends Report_Type {

	private static $course_lesson_count = [];

	public static function key(): string {
		return 'course_progress';
	}

	public static function label(): string {
		return __( 'Course progress', 'thrive-apprentice' );
	}

	/**
	 * Return an array of all filters supported by fields
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		$filters = [];

		foreach ( [ Created::class, Course_Id::class, User_Id::class ] as $field_class ) {
			/** @var $field_class Event_Field */
			$filters[ $field_class::key() ] = [
				'label' => $field_class::get_label(),
				'type'  => $field_class::get_filter_type(),
			];
		}

		return $filters;
	}

	public static function get_table_data( $query ): array {
		$current_events = Lesson_Complete::get_data( $query );

		$current_events['number_of_items'] = Lesson_Complete::count_data( $query );

		if ( empty( $current_events['items'] ) ) {
			/* there's not data to use */
			return $current_events;
		}

		$query['filters'][ Created::key() ] = [
			/* get events until start date */
			'to' => Created::minus_one_day( $query['filters']['date']['from'] ),
		];
		/* but only for the users that have data in the time period we are interested */
		$query['filters'][ Member_Id::key() ] = array_keys( $current_events['labels'][ Member_Id::key() ]['values'] );

		$past_events = Lesson_Complete::get_data( [
			'filters'  => $query['filters'],
			'group_by' => [ Member_Id::key(), Course_Id::key() ],
			'fields'   => [ Member_Id::key(), Course_Id::key() ],
		] );

		$user_completed_lessons = [];
		/* for each user count how many lessons he completed in each course */
		foreach ( $past_events['items'] as $item ) {
			$user_completed_lessons[ $item[ Member_Id::key() ] ][ $item[ Course_Id::key() ] ] = (int) $item['count'];
		}

		$course_lesson_count = [];

		foreach ( $current_events['items'] as &$item ) {
			if ( ! isset( $course_lesson_count[ $item[ Course_Id::key() ] ] ) ) {
				$course = new \TVA_Course_V2( (int) $item[ Course_Id::key() ] );

				$course_lesson_count[ $item[ Course_Id::key() ] ] = $course->count_lessons();
			}

			if ( empty( $user_completed_lessons[ $item[ Member_Id::key() ] ][ $item[ Course_Id::key() ] ] ) ) {
				$user_completed_lessons[ $item[ Member_Id::key() ] ][ $item[ Course_Id::key() ] ] = 0;
			}
			$user_completed_lessons[ $item[ Member_Id::key() ] ][ $item[ Course_Id::key() ] ] ++;
			/* update progress each time a lesson has been completed */
			$item['progress'] = $user_completed_lessons[ $item[ Member_Id::key() ] ][ $item[ Course_Id::key() ] ] . '/' . $course_lesson_count[ $item[ Course_Id::key() ] ];
		}

		$current_events['items'] = static::order_items( $current_events['items'], $query, $current_events['labels'] );

		$current_events['labels']['progress'] = [
			'key'  => 'progress',
			'text' => __( 'Progress', 'thrive-apprentice' ),
		];
		/* update some labels for table */
		$current_events['labels'][ Lesson_Id::key() ]['text'] = __( 'Lesson completed', 'thrive-apprentice' );

		$current_events['images'] = static::get_custom_data_images( $current_events['items'], [ User_Id::class ] );

		return $current_events;
	}

	/**
	 * Course progress
	 *    > get completed lesson events for the time period
	 *    > calculate the course progress until then for each user that has activity in this time period
	 *    > day by day, get add completed lessons and calculate the progress percentage
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_chart_data( $query = [] ): array {
		$current_events = Lesson_Complete::get_data( [
			'filters'  => $query['filters'],
			'group_by' => [ Course_Id::key(), User_Id::key(), Created::key() ],
		] );

		$past_events = Lesson_Complete::get_data( [
			'filters'  => [
				'date'         => [
					'to' => Created::minus_one_day( $query['filters']['date']['from'] ),
				],
				User_Id::key() => array_unique( array_map( static function ( $item ) {
					return $item[ User_Id::key() ];
				}, $current_events['items'] ) ),
			],
			'group_by' => [ Course_Id::key(), User_Id::key() ],
		] );

		$users = [];
		foreach ( $past_events['items'] as $users_lesson_event ) {
			/* for each user, in each course, save the number of lessons that are completed until the start date */
			$users[ $users_lesson_event[ User_Id::key() ] ][ $users_lesson_event[ Course_Id::key() ] ] = $users_lesson_event['count'];
		}

		$events_by_date = [];
		foreach ( $current_events['items'] as $item ) {
			if ( empty( $events_by_date[ $item[ Created::key() ] ] ) ) {
				$events_by_date[ $item[ Created::key() ] ] = [];
			}
			/* group events by date */
			$events_by_date[ $item[ Created::key() ] ][] = $item;
		}

		/* it's important for the events to be sorted by date so we can calculate the progress */
		ksort( $events_by_date );

		$items = [];
		foreach ( $events_by_date as $date => $events ) {
			foreach ( $events as $event ) {
				if ( empty( $users[ $event[ User_Id::key() ] ][ $event[ Course_Id::key() ] ] ) ) {
					$users[ $event[ User_Id::key() ] ][ $event[ Course_Id::key() ] ] = 0;
				}

				/* increase the number of completed lessons inside the course */
				$users[ $event[ User_Id::key() ] ][ $event[ Course_Id::key() ] ] += $event['count'];
			}

			$items[] = [
				'date'  => $date,
				'count' => (int) static::calculate_course_progress_average( $users ),
			];
		}

		return [
			'labels'       => $current_events['labels'],
			'items'        => $items,
			'tooltip_text' => static::get_tooltip_text(),
		];
	}

	/**
	 * Only data for card
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_card_data( $query = [] ): array {
		$data = static::get_chart_data( $query );

		$chart_data = empty( $query['has_chart'] ) ? [] : $data;

		if ( empty( $data['items'] ) ) {
			$chart_data ['count'] = '0%';
		} else {
			$chart_data ['count'] = $data['items'][ count( $data['items'] ) - 1 ]['count'] . '%';
		}

		$chart_data['no_data'] = empty( $data['items'] ) ? 1 : 0;

		return $chart_data;
	}

	public static function get_tooltip_text(): string {
		return static::label() . ': <strong>{number}%</strong>';
	}

	/**
	 *  Calculate the average of course progress - ( complete / ( completed + started ) )
	 *
	 * @param $users
	 *
	 * @return float|int
	 */
	private static function calculate_course_progress_average( $users ) {
		$course_progress = 0;

		foreach ( $users as $user_courses ) {
			$user_progress = 0;
			$course_count  = 0;

			foreach ( $user_courses as $course_id => $completed_lessons ) {
				$lesson_count = static::get_course_lesson_count( $course_id );

				if ( $lesson_count > 0 ) {
					$user_progress += $completed_lessons * 100 / $lesson_count;
					$course_count ++;
				}
			}

			if ( $course_count > 0 ) {
				$course_progress += $user_progress / $course_count;
			}
		}

		return $course_progress / count( $users );
	}

	/**
	 * calculate the number of lessons in each course and cache it.
	 *
	 * @param $course_id
	 *
	 * @return mixed
	 */
	private static function get_course_lesson_count( $course_id ) {
		if ( empty( static::$course_lesson_count[ $course_id ] ) ) {
			$course = new \TVA_Course_V2( (int) $course_id );

			static::$course_lesson_count[ $course_id ] = $course->count_lessons();
		}

		return static::$course_lesson_count[ $course_id ];
	}
}
