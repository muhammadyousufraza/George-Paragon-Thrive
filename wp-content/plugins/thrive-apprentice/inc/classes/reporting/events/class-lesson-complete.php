<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\Events;

use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Lesson_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVA_Course_V2;
use TVE\Reporting\Event;
use TVE\Reporting\EventFields\Event_Type;
use TVE\Reporting\Traits\Report;

class Lesson_Complete extends Event {

	use Report {
		Report::get_group_by as _get_group_by;
	}

	public static function key(): string {
		return 'tva_lesson_complete';
	}

	public static function get_tooltip_text(): string {
		return '<strong>{number}</strong> ' . __( 'completed lessons', 'thrive-apprentice' );
	}

	public static function get_item_id_field(): string {
		return Lesson_Id::class;
	}

	public static function get_user_id_field(): string {
		return Member_Id::class;
	}

	public static function get_extra_int_field_1() {
		return Course_Id::class;
	}

	public static function label(): string {
		return __( 'Lesson Completed', 'thrive-apprentice' );
	}

	public static function get_group_by(): array {
		return array_filter( static::_get_group_by(), static function ( $value, $key ) {
			return strpos( $key, 'post_id' ) === false;
		}, ARRAY_FILTER_USE_BOTH );
	}

	public static function register_action() {
		add_action( 'thrive_apprentice_lesson_complete', static function ( $lesson_details, $user_details ) {
			$event = new static( [
				'item_id'   => $lesson_details['lesson_id'],
				'user_id'   => empty( $user_details['user_id'] ) ? 0 : $user_details['user_id'],
				'post_id'   => $lesson_details['lesson_id'],
				'course_id' => $lesson_details['course_id'],
			] );

			$event->log();
		}, 10, 2 );
	}

	/**
	 * Event description - used for user timeline
	 *
	 * @return string
	 */
	public function get_event_description(): string {
		$lesson = $this->get_field( 'item_id' )->get_title();
		$course = $this->get_field( 'course_id' )->get_title();

		return " completed lesson \"$lesson\" in the course \"$course\".";
	}

	/**
	 * Completion rate for lessons
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_completion_rate_data( $query ) {
		$active_courses = static::get_data( [
			'filters'    => $query['filters'],
			'group_by'   => [ Course_Id::key() ],
			'event_type' => [ Lesson_Start::key(), Lesson_Complete::key() ],
		] );

		$filters = [
			Course_Id::key() => array_map( static function ( $item ) {
				return $item[ Course_Id::key() ];
			}, $active_courses['items'] ),
		];

		if ( isset( $query['filters'][ Member_Id::key() ] ) ) {
			$filters[ Member_Id::key() ] = $query['filters'][ Member_Id::key() ];
		}

		$lesson_data = static::get_data( [
			/* count unique events for users - just in case a user has started a lesson multiple times */
			'count'      => 'DISTINCT CONCAT(' . Member_Id::key() . ',"-",' . static::get_field_table_col( Lesson_Id::key() ) . ')',
			'filters'    => $filters,
			'group_by'   => [ Course_Id::key(), Lesson_Id::key(), Event_Type::key() ],
			'event_type' => [ Lesson_Start::key(), Lesson_Complete::key() ],
		] );

		$courses = [];

		foreach ( $lesson_data['items'] as $item ) {
			$courses[ $item[ Course_Id::key() ] ][ $item[ Lesson_Id::key() ] ][ $item[ Event_Type::key() ] ] = $item['count'];
		}

		$items        = [];
		$label_values = [];

		foreach ( $courses as $course_id => $lessons ) {
			$course         = new TVA_Course_V2( (int) $course_id );
			$course_lessons = $course->get_all_lessons();

			foreach ( $course_lessons as $index => $lesson ) {
				if ( empty( $lessons[ $lesson->ID ][ Lesson_Start::key() ] ) || empty( $lessons[ $lesson->ID ][ Lesson_Complete::key() ] ) ) {
					$completion_rate = 0;
				} else {
					$completion_rate = round( 100 * $lessons[ $lesson->ID ][ Lesson_Complete::key() ] / $lessons[ $lesson->ID ][ Lesson_Start::key() ], 2 );
				}

				$items[] = [
					Course_Id::key() => $course_id,
					Lesson_Id::key() => $index + 1,
					'count'          => $completion_rate,
					'tooltip'        => [
						'ratio'     => $completion_rate,
						'lesson'    => $lesson->post_title,
						'started'   => $lessons[ $lesson->ID ][ Lesson_Start::key() ] ?? 0,
						'completed' => $lessons[ $lesson->ID ][ Lesson_Complete::key() ] ?? 0,
					],
				];

				$label_values[ $index ] = Lesson_Id::get_label( true ) . ' ' . ( $index + 1 );
			}
		}

		if ( empty( $filters[ Member_Id::key() ] ) || count( $filters[ Member_Id::key() ] ) > 1 ) {
			$tooltip_text = '<br>"<strong>{lesson}</strong>"<br>' .
							'<strong>{started}</strong> members started the lesson.<br>' .
							'<strong>{completed}</strong> members completed the lesson.<br>' .
							'<strong>{ratio}%</strong> completion rate.';
		} else {
			$tooltip_text = '"<strong>{lesson}</strong>"';
		}

		return [
			'items'        => $items,
			'labels'       => [
				Course_Id::key() => $lesson_data['labels'][ Course_Id::key() ],
				Lesson_Id::key() => Lesson_Id::get_label_structure( $label_values ),
			],
			'tooltip_text' => $tooltip_text,
		];
	}
}
