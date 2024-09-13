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
use TVA\Reporting\Utils;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Report_Type;

class Engagement_Type extends Report_Type {
	const KEY = 'engagement_type';

	const LESSON_COMPLETION_KEY    = 'lesson_completions';
	const COMMENTS_KEY             = 'comments';
	const QUIZ_COMPLETION_KEY      = 'quiz_completions';
	const ENROLLMENTS_KEY          = 'enrollments';
	const CERTIFICATE_DOWNLOAD_KEY = 'certificate_downloads';
	const CERTIFICATE_VERIFY_KEY   = 'certificate_verifications';
	const ASSESSMENT_SUBMIT_KEY    = 'assessment_submissions';
	const ASSESSMENT_PASS_KEY      = 'assessment_passes';
	const ASSESSMENT_FAIL_KEY      = 'assessment_fails';
	const VIDEO_STARTED_KEY        = 'video_started';
	const VIDEO_COMPLETED_KEY      = 'video_complete';

	public static function key(): string {
		return static::KEY;
	}

	public static function label(): string {
		return __( 'Total engagements', 'thrive-apprentice' );
	}

	/**
	 * Return an array of all filters supported by fields
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		$filters = [];

		foreach ( [ Created::class, Course_Id::class, Member_Id::class ] as $field_class ) {
			/** @var $field_class Event_Field */
			$filters[ $field_class::key() ] = [
				'label' => $field_class::get_label(),
				'type'  => $field_class::get_filter_type(),
			];
		}

		$filters['engagement_type'] = [
			'label' => __( 'Engagement type ', 'thrive-apprentice' ),
			'type'  => 'multiple-select',
		];

		return $filters;
	}

	/**
	 * @param $query
	 * @param $is_table
	 *
	 * @return array
	 */
	public static function get_data( $query = [], $is_table = false ): array {
		$items        = [];
		$label_values = [
			Created::key() => [],
			User_Id::key() => [],
		];

		foreach ( static::get_engagement_types() as $engagement_key => $engagement_data ) {
			if ( static::should_include_engagement_type( $engagement_key, $query ) ) {
				if ( isset( $engagement_data['extra_check_fn'] ) && ! $engagement_data['extra_check_fn']( $is_table ) ) {
					continue;
				}

				$engagement_items = $engagement_data['items_callback']( $query, $is_table, $label_values );

				if ( $engagement_data['should_format_for_reporting'] ) {
					$engagement_items = static::prepare_engagement_items( $engagement_items, $label_values, $engagement_key );
				}

				$items = array_merge( $items, $engagement_items );
			}
		}

		$data = [
			'items'  => $items,
			'labels' => [
				Created::key() => Created::get_label_structure( $label_values[ Created::key() ] ),
				User_Id::key() => User_Id::get_label_structure( $label_values[ User_Id::key() ] ),
				static::KEY    => static::get_engagement_type_labels(),
			],
		];

		if ( ! $is_table ) {
			$data['count'] = array_reduce( $items, static function ( $total, $item ) {
				return $total + (int) $item['count'];
			}, 0 );
		}

		return $data;
	}

	public static function get_table_data( $query ): array {
		$data = static::get_data( $query, true );

		if ( static::should_include_engagement_type( static::ENROLLMENTS_KEY, $query ) ) {
			$label_values = [
				Created::key() => [],
				User_Id::key() => [],
			];

			$items = [];

			foreach ( History_Table::get_instance()->get_course_enrollments_table( [ 'where' => $query['filters'] ] ) as $enrollment ) {
				$date    = $enrollment['created'];
				$user_id = $enrollment['user_id'];

				$items[] = [
					Created::key() => $date,
					User_Id::key() => $user_id,
					static::KEY    => static::ENROLLMENTS_KEY,
				];

				$label_values[ Created::key() ][ $date ]    = Created::format_value( $date );
				$label_values[ User_Id::key() ][ $user_id ] = ( new User_Id( $user_id ) )->get_title();
			}

			$data['items'] = array_merge( $data['items'], $items );

			/* we need to keep the keys which in this case are number and can be mistaken by indexes */
			$data['labels'][ Created::key() ]['values'] = $data['labels'][ Created::key() ]['values'] + $label_values[ Created::key() ];
			$data['labels'][ User_Id::key() ]['values'] = $data['labels'][ User_Id::key() ]['values'] + $label_values[ User_Id::key() ];
		}

		$data['number_of_items'] = count( $data['items'] );

		if ( empty( $query['order_by'] ) ) {
			$query['order_by'] = Created::key();
		}

		$data['items'] = static::order_items( $data['items'], $query, $data['labels'] );
		$data['items'] = static::slice_items( $data['items'], $query );

		$data['images'] = static::get_custom_data_images( $data['items'], [ User_Id::class ] );

		return $data;
	}

	/**
	 * Returns a custom structure made for the 'engagement type count' pie chart.
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_pie_data( $query ) {
		$lesson_ids = Utils::get_lesson_ids_for_courses( $query );

		$items = [
			[
				static::KEY => static::LESSON_COMPLETION_KEY,
				'count'     => Utils::get_completed_lessons( $query, 'count' ),
			],
			[
				static::KEY => static::COMMENTS_KEY,
				'count'     => Utils::get_lesson_comments( $query, $lesson_ids ),
			],
			[
				static::KEY => static::ENROLLMENTS_KEY,
				'count'     => array_reduce( History_Table::get_instance()->get_course_enrollments( $query['filters'] ), static function ( $total, $enrollment ) {
					return $total + (int) $enrollment['status'];
				}, 0 ),
			],
			[
				static::KEY => static::CERTIFICATE_DOWNLOAD_KEY,
				'count'     => Utils::get_certificate_downloads( $query, 'count' ),
			],
			[
				static::KEY => static::CERTIFICATE_VERIFY_KEY,
				'count'     => Utils::get_certificate_verifications( $query, 'count' ),
			],
			[
				static::KEY => static::ASSESSMENT_SUBMIT_KEY,
				'count'     => Utils::get_assessment_submissions( $query, 'count' ),
			],
			[
				static::KEY => static::ASSESSMENT_PASS_KEY,
				'count'     => Utils::get_assessment_passes( $query, 'count' ),
			],
			[
				static::KEY => static::ASSESSMENT_FAIL_KEY,
				'count'     => Utils::get_assessment_fails( $query, 'count' ),
			],
			[
				static::KEY => static::VIDEO_STARTED_KEY,
				'count'     => Utils::get_started_videos( $query, $lesson_ids, 'count' ),
			],
			[
				static::KEY => static::VIDEO_COMPLETED_KEY,
				'count'     => Utils::get_completed_videos( $query, $lesson_ids, 'count' ),
			],
		];

		if ( static::has_quiz_reports() ) {
			$items[] = [
				static::KEY => static::QUIZ_COMPLETION_KEY,
				'count'     => Utils::get_completed_quizzes( $query, $lesson_ids, 'count' ),
			];
		}

		return [
			'labels'       => [
				static::KEY => static::get_engagement_type_labels(),
			],
			'items'        => $items,
			'count'        => array_reduce( $items, static function ( $total, $item ) {
				return $total + (int) $item['count'];
			}, 0 ),
			'tooltip_text' => __( 'Engagements', 'thrive-apprentice' ) . ': <strong>{number}</strong>',
		];
	}

	/**
	 * @param $key
	 * @param $query
	 *
	 * @return bool
	 */
	public static function should_include_engagement_type( $key, $query ) {
		$type = empty( $query['filters']['engagement_type'] ) ? [] : $query['filters']['engagement_type'];

		return empty( $type ) || ( $key === $type ) || ( is_array( $type ) && in_array( $key, $type ) );
	}

	/**
	 * Prepare items for report display
	 *
	 * @param $items
	 * @param $labels
	 * @param $key
	 *
	 * @return array|array[]
	 */
	protected static function prepare_engagement_items( $items, &$labels, $key ) {
		return array_map( static function ( $item ) use ( $key, &$labels ) {
			$date    = $item[ Created::key() ];
			$user_id = $item[ User_Id::key() ];

			$labels[ Created::key() ][ $date ] = Created::format_value( $date );

			if ( empty( $labels[ User_Id::key() ][ $user_id ] ) ) {
				$labels[ User_Id::key() ][ $user_id ] = ( new User_Id( $user_id ) )->get_title();
			}

			return [
				Created::key() => $date,
				User_Id::key() => $user_id,
				'count'        => $item['count'] ?? 1,
				static::KEY    => $key,
			];
		}, $items );
	}

	/**
	 * @param $query
	 * @param $lesson_ids
	 * @param $label_values
	 * @param $is_table
	 *
	 * @return array
	 */
	public static function get_comment_items( $query, $lesson_ids, &$label_values, $is_table ) {
		$items = [];

		foreach ( Utils::get_lesson_comments( $query, $lesson_ids, false, ! $is_table ) as $comment ) {
			$date    = $comment['date'];
			$user_id = $comment['user_id'];

			$item_data = [
				Created::key() => $date,
				User_Id::key() => $user_id,
				static::KEY    => static::COMMENTS_KEY,
			];

			if ( ! $is_table ) {
				$item_data['count'] = $comment['count'];
			}

			$items[]                                    = $item_data;
			$label_values[ Created::key() ][ $date ]    = Created::format_value( $date );
			$label_values[ User_Id::key() ][ $user_id ] = is_numeric( $user_id ) ? ( new User_Id( $user_id ) )->get_title() : $user_id;
		}

		return $items;
	}

	/**
	 * @param $query
	 * @param $label_values
	 *
	 * @return array
	 */
	public static function get_enrollment_items( $query, &$label_values ) {
		$items = [];

		$enrollments = History_Table::get_instance()->get_course_enrollments_table( [ 'where' => $query['filters'], 'select' => [ Created::get_query_select_field() ] ] );

		foreach ( $enrollments as $enrollment ) {
			$date = $enrollment['date'];

			if ( empty( $items[ $date ] ) ) {
				$items[ $date ] = [
					Created::key() => $date,
					'count'        => 0,
					static::KEY    => static::ENROLLMENTS_KEY,
				];

				$label_values[ Created::key() ][ $date ] = Created::format_value( $date );
			}

			$items[ $date ]['count'] ++;
		}

		return array_values( $items );
	}

	public static function get_engagement_types() {
		return [
			static::LESSON_COMPLETION_KEY    => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_completed_lessons( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			static::COMMENTS_KEY             => [
				'items_callback'              => static function ( $query, $is_table ) {
					$lesson_ids = Utils::get_lesson_ids_for_courses( $query );

					return static::get_comment_items( $query, $lesson_ids, $label_values, $is_table );
				},
				'should_format_for_reporting' => false,
			],
			static::ENROLLMENTS_KEY          => [
				'items_callback'              => static function ( $query, $is_table, &$label_values ) {
					return static::get_enrollment_items( $query, $label_values );
				},
				'should_format_for_reporting' => false,
				'extra_check_fn'              => static function ( $is_table ) {
					/* if the report is a table, this is handled separately */
					return ! $is_table;
				},
			],
			static::QUIZ_COMPLETION_KEY      => [
				'items_callback'              => static function ( $query, $is_table ) {
					$lesson_ids = Utils::get_lesson_ids_for_courses( $query );

					return Utils::get_completed_quizzes( $query, $lesson_ids, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
				'extra_check_fn'              => static function () {
					return static::has_quiz_reports();
				},
			],
			static::CERTIFICATE_DOWNLOAD_KEY => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_certificate_downloads( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			static::CERTIFICATE_VERIFY_KEY   => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_certificate_verifications( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			static::ASSESSMENT_SUBMIT_KEY    => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_assessment_submissions( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			static::ASSESSMENT_PASS_KEY      => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_assessment_passes( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			static::ASSESSMENT_FAIL_KEY      => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_assessment_fails( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			/* todo: not sure if this should be an engagement type or not....anyway here it is :)) */
			static::VIDEO_STARTED_KEY        => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_started_videos( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
			static::VIDEO_COMPLETED_KEY      => [
				'items_callback'              => static function ( $query, $is_table ) {
					return Utils::get_completed_videos( $query, $is_table ? 'table' : 'chart' );
				},
				'should_format_for_reporting' => true,
			],
		];
	}

	protected static function has_quiz_reports() {
		return tve_dash_is_plugin_active( 'thrive-quiz-builder' ) && class_exists( '\TQB\Reporting\Events\Quiz_Completed', false );
	}

	/**
	 * @return array
	 */
	public static function get_engagement_type_labels() {
		return [
			'key'    => static::KEY,
			'text'   => __( 'Engagement type', 'thrive-apprentice' ),
			'values' => [
				static::LESSON_COMPLETION_KEY    => __( 'Lesson completions', 'thrive-apprentice' ),
				static::COMMENTS_KEY             => __( 'Comments', 'thrive-apprentice' ),
				static::QUIZ_COMPLETION_KEY      => __( 'Quiz completions', 'thrive-apprentice' ),
				static::ENROLLMENTS_KEY          => __( 'Enrollments', 'thrive-apprentice' ),
				static::CERTIFICATE_DOWNLOAD_KEY => __( 'Certificate downloads', 'thrive-apprentice' ),
				static::CERTIFICATE_VERIFY_KEY   => __( 'Certificate verifications', 'thrive-apprentice' ),
				static::ASSESSMENT_SUBMIT_KEY    => __( 'Assessment submissions', 'thrive-apprentice' ),
				static::ASSESSMENT_PASS_KEY      => __( 'Assessment passes', 'thrive-apprentice' ),
				static::ASSESSMENT_FAIL_KEY      => __( 'Assessment fails', 'thrive-apprentice' ),
				static::VIDEO_STARTED_KEY        => __( 'Started video', 'thrive-apprentice' ),
				static::VIDEO_COMPLETED_KEY      => __( 'Completed video', 'thrive-apprentice' ),
			],
		];
	}
}
