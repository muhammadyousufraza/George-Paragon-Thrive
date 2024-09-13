<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting;

use TQB\Reporting\Events\Quiz_Completed;
use TVA\Reporting\Events\Assessment_Failed;
use TVA\Reporting\Events\Assessment_Passed;
use TVA\Reporting\Events\Assessment_Submitted;
use TVA\Reporting\Events\Certificate_Download;
use TVA\Reporting\Events\Certificate_Verify;
use TVA\Reporting\Events\Lesson_Complete;
use TVA\Reporting\Events\Video_Completed;
use TVA\Reporting\Events\Video_Start;
use TVA_Const;
use TVA_Course_V2;
use TVA_Manager;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Post_Id;
use TVE\Reporting\EventFields\User_Id;
use WP_Comment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Utils {
	/**
	 * @param $query
	 *
	 * @return int|TVA_Course_V2[]
	 */
	public static function get_courses( $query ) {
		$course_ids = [];
		$status     = '';

		if ( ! empty( $query['filters']['course_id'] ) ) {
			$course_ids = is_array( $query['filters']['course_id'] ) ? $query['filters']['course_id'] : explode( ',', $query['filters']['course_id'] );
		}

		if ( ! empty( $query['filters']['course_status'] ) && count( $query['filters']['course_status'] ) === 1 ) {
			$status = $query['filters']['course_status'][0];
		}

		return TVA_Course_V2::get_items( [
			'status'  => $status,
			'include' => $course_ids,
		] );
	}

	/**
	 * @param $filters
	 * @param $group_by
	 *
	 * @return mixed
	 */
	public static function get_completed_lesson_count( $filters, $group_by ) {
		$lesson_count_map = [];

		$data = Lesson_Complete::get_data( [
			'filters'  => $filters,
			'group_by' => $group_by,
		] );

		foreach ( $data['items'] as $item ) {
			$lesson_count_map[ $item[ $group_by[0] ] ][ $item[ $group_by[1] ] ] = (int) $item['count'];
		}

		return $lesson_count_map;
	}

	/**
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_lesson_count_by_course( $query ) {
		$course_lesson_map = [];

		foreach ( Utils::get_courses( $query ) as $course ) {
			$lesson_count = $course->count_lessons();

			if ( ! empty( $lesson_count ) ) {
				$course_lesson_map[ $course->term_id ] = $course->count_lessons();
			}
		}

		return $course_lesson_map;
	}

	/**
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_lesson_ids_for_courses( $query ) {
		$course_ids = array_map( static function ( $course ) {
			return $course->ID;
		}, Utils::get_courses( $query ) );

		return array_map( static function ( $post ) {
			return $post->ID;
		}, TVA_Manager::get_course_direct_items( $course_ids, [ 'post_type' => TVA_Const::LESSON_POST_TYPE ] ) );
	}

	/**
	 * @param $sql_where
	 * @param $date_col_name
	 * @param $query
	 *
	 * @return mixed|string
	 */
	public static function apply_date_filter( $sql_where, $date_col_name, $query ) {
		if ( ! empty( $query['filters']['date']['from'] ) ) {
			$sql_where .= sprintf( " AND DATE(%s) >= '%s'", $date_col_name, $query['filters']['date']['from'] );
		}
		if ( ! empty( $query['filters']['date']['to'] ) ) {
			$sql_where .= sprintf( " AND DATE(%s) <= '%s'", $date_col_name, $query['filters']['date']['to'] );
		}

		return $sql_where;
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_completed_lessons( $query, $report_type = 'count' ) {
		return static::get_event_data( Lesson_Complete::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_started_videos( $query, $report_type = 'count' ) {
		return static::get_event_data( Video_Start::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_completed_videos( $query, $report_type = 'count' ) {
		return static::get_event_data( Video_Completed::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $lesson_ids
	 * @param $report_type
	 *
	 * @return mixed
	 */
	public static function get_completed_quizzes( $query, $lesson_ids, $report_type = 'count' ) {
		$query = [
			'fields'   => [ Created::key(), User_Id::key() ],
			'group_by' => $report_type === 'chart' ? [ Created::key() ] : [],
			'filters'  => [
				Post_Id::key() => $lesson_ids,
				Created::key() => $query['filters'][ Created::key() ],
				User_Id::key() => $query['filters'][ User_Id::key() ],
			],
		];

		if ( $report_type === 'count' ) {
			$quizzes = Quiz_Completed::count_data( $query );
		} else {
			$data    = Quiz_Completed::get_data( $query );
			$quizzes = $data['items'];
		}

		return $quizzes;
	}

	/**
	 * @param $query
	 * @param $lesson_ids
	 * @param $just_count
	 * @param $group_comments_by_date
	 *
	 * @return int|int[]|WP_Comment[]
	 */
	public static function get_lesson_comments( $query, $lesson_ids, $just_count = true, $group_comments_by_date = true ) {
		global $wpdb;

		$db_instance    = $wpdb;
		$comments_table = $db_instance->prefix . 'comments';
		$where          = sprintf( '%s IN ( %s )', 'comment_post_ID', implode( ', ', $lesson_ids ) );
		$group_by       = '';

		if ( ! empty( $query['filters'][ User_Id::key() ] ) ) {
			$where .= sprintf( ' AND %s IN ( %s )', 'comment_author', implode( ', ', $query['filters'][ User_Id::key() ] ) );
		}

		if ( $just_count ) {
			$select = 'comment_ID';
		} else {
			$date_format = str_replace( 'created', 'comment_date', Created::get_query_select_field() );

			/* if the user_id is 0, get the comment_author field instead */
			$select = "$date_format, IFNULL(NULLIF(user_id, 0),comment_author) as user_id";

			if ( $group_comments_by_date ) {
				$select .= ', COUNT(comment_ID) as count';

				$group_by = ' GROUP BY ' . str_replace( ' AS date', '', $date_format );
			}
		}

		$where = Utils::apply_date_filter( $where, 'comment_date', $query );
		$where = $db_instance->prepare( $where );

		$results = $db_instance->get_results( "SELECT $select FROM $comments_table WHERE $where $group_by", ARRAY_A );

		return $just_count ? $db_instance->num_rows : $results;
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_certificate_downloads( $query, $report_type = 'count' ) {
		return static::get_event_data( Certificate_Download::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_certificate_verifications( $query, $report_type = 'count' ) {
		return static::get_event_data( Certificate_Verify::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_assessment_submissions( $query, $report_type = 'count' ) {
		return static::get_event_data( Assessment_Submitted::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_assessment_passes( $query, $report_type = 'count' ) {
		return static::get_event_data( Assessment_Passed::class, $query, $report_type );
	}

	/**
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_assessment_fails( $query, $report_type = 'count' ) {
		return static::get_event_data( Assessment_Failed::class, $query, $report_type );
	}

	/**
	 * @param $event_class
	 * @param $query
	 * @param $report_type
	 *
	 * @return int|mixed
	 */
	public static function get_event_data( $event_class, $query, $report_type = 'count' ) {
		$data = $event_class::get_data( [
			'fields'   => [ Created::key(), User_Id::key() ],
			'group_by' => $report_type === 'chart' ? [ Created::key() ] : [],
			'filters'  => $query['filters'],
		] );

		return $report_type === 'count' ? count( $data['items'] ) : $data['items'];
	}

	/**
	 * @param $array
	 *
	 * @return array|false|string|string[]
	 */
	public static function encode_array( $array ) {
		return str_replace( [ '"', '[', ']' ], [ "'", '|{|', '|}|' ], json_encode( $array ) );
	}
}
