<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Assessments;

use TVA_Assessment;
use TVA_Manager;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Inbox {
	/**
	 * Cache for the assessment objects
	 *
	 * @var array
	 */
	public static $ASSESSMENT_CACHE = [];

	public static $DEFAULT_ARGS = [
		'post_type'      => TVA_User_Assessment::POST_TYPE,
		'posts_per_page' => - 1,
		'post_status'    => 'any',
	];

	/**
	 * Get the assessment object from the cache or create a new one
	 *
	 * @param $id
	 *
	 * @return TVA_Assessment
	 */
	public static function get_assessment( $id ) {
		if ( ! isset( static::$ASSESSMENT_CACHE[ $id ] ) ) {
			static::$ASSESSMENT_CACHE[ $id ] = new TVA_Assessment( $id );
		}

		return static::$ASSESSMENT_CACHE[ $id ];
	}

	public static function get_assessments() {
		$posts = TVA_Manager::get_assessments( [
				'meta_query' => [
					[
						'key'     => 'tva_is_demo',
						'compare' => 'NOT EXISTS',
					],
				],
			]

		);

		$assessments = [];
		foreach ( $posts as $post ) {
			$assessments[] = [
				'id'   => $post->ID,
				'text' => $post->post_title,
			];
		}


		return $assessments;
	}

	/**
	 * Make sure that the filters are in the correct format
	 *
	 * @param $filters
	 *
	 * @return mixed
	 */
	public static function prepare_filters( $filters ) {
		if ( empty( $filters['meta_query'] ) ) {
			$filters['meta_query'] = [
				[
					'key'     => 'tva_is_demo',
					'compare' => 'NOT EXISTS',
				],
			];
		}

		$meta_queries = [
			[
				'key'     => TVA_User_Assessment::OUTDATED_META,
				'compare' => isset( $filters['outdated'] ) && $filters['outdated'] ? 'EXISTS' : 'NOT EXISTS',
			],
		];

		if ( ! empty( $filters['assessment_status'] ) ) {
			$meta_queries[] = [
				'key'     => TVA_User_Assessment::STATUS_META,
				'value'   => $filters['assessment_status'],
				'compare' => '=',
			];
		}

		if ( ! empty( $filters['course_ids'] ) ) {
			$meta_queries[] = [
				'key'     => TVA_User_Assessment::COURSE_META,
				'value'   => $filters['course_ids'],
				'compare' => 'IN',
			];
			unset( $filters['course_ids'] );
		}


		if ( count( $meta_queries ) > 1 ) {
			$meta_queries['relation'] = 'AND';
		}

		$filters['meta_query'] = array_merge( $filters['meta_query'], $meta_queries );

		if ( ! empty( $filters['assessment_ids'] ) ) {
			$filters['post_parent__in'] = $filters['assessment_ids'];
			unset( $filters['assessment_ids'] );
		}

		if ( ! empty( $filters['student_ids'] ) ) {
			$filters['author__in'] = $filters['student_ids'];
			unset( $filters['student_ids'] );
		}

		if ( ! empty( $filters['limit'] ) ) {
			$filters['posts_per_page'] = $filters['limit'];
			unset( $filters['limit'] );
		}
		if ( ! empty( $filters['page'] ) ) {
			$filters['paged'] = $filters['page'];
			unset( $filters['page'] );
		}

		return $filters;
	}

	/**
	 * Get assessments count based on filters
	 *
	 * @param $filters
	 *
	 * @return int
	 */
	public static function get_assessment_count( $filters ) {
		$filters = static::prepare_filters( $filters );
		$args    = array_merge(
			static::$DEFAULT_ARGS,
			$filters
		);
		$query   = new WP_Query( $args );

		return $query->post_count;
	}

	/**
	 * Get the submissions for the assessments based on the filters
	 *
	 * @param $filters
	 *
	 * @return array
	 */
	public static function get_assessment_submissions( $filters ) {
		$filters          = static::prepare_filters( $filters );
		$submission_posts = get_posts( array_merge(
			static::$DEFAULT_ARGS,
			$filters
		) );

		$content = [];

		foreach ( $submission_posts as $submission_post ) {
			$submission = ( new TVA_User_Assessment( $submission_post ) )->get_inbox_details();
			/**
			 * For the submissions that are not outdated we need to add the assessment details
			 */
			if ( ! isset( $filters['outdated'] ) ) {
				$assessment               = static::get_assessment( $submission_post->post_parent );
				$submission['assessment'] = $assessment->get_inbox_details();
			}

			$content[] = $submission;
		}

		return $content;
	}

	/**
	 * Get the authors of the assessments for users that still exist
	 *
	 * @return array|array[]
	 */
	public static function get_authors() {
		global $wpdb;
		$users = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT posts.post_author FROM {$wpdb->posts} as posts INNER JOIN {$wpdb->users} as users ON posts.post_author = users.ID WHERE posts.post_type = %s AND users.ID IS NOT NULL",
				TVA_User_Assessment::POST_TYPE
			)
		);

		//get display name too
		return array_map( function ( $user_id ) {
			return [
				'id'   => $user_id,
				'name' => get_the_author_meta( 'display_name', $user_id ),
			];
		}, $users );
	}
}
