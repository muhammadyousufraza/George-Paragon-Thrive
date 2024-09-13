<?php

namespace TVA\Architect\Assessment;

use Exception;
use TVA\Assessments\TVA_User_Assessment;
use TVA_Assessment;
use TVA_Const;
use TVA_Manager;
use WP_Post;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Assessment Result List class
 */
class Result {

	const LIST_IDENTIFIER = '.tva-assessment-result-list';
	const ITEM_IDENTIFIER = '.tva-assessment-result-item';

	const DEFAULT_NO_OF_ITEMS = 10;

	/**
	 * Codes needed to calculate the states of the result item
	 *
	 * @var int[]
	 */
	private static $codes = [
		TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT => 2,
		TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED   => 1,
		TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED   => 0,
	];

	/**
	 * @param TVA_User_Assessment $user_assessment
	 *
	 * @return int
	 */
	public static function get_active_state( $user_assessment ) {
		if ( isset( static::$codes[ $user_assessment->status ] ) ) {
			return (int) static::$codes[ $user_assessment->status ];
		}

		return 0;
	}

	/**
	 * Fetches the default content
	 *
	 * @return string
	 */
	public static function get_default_content() {
		ob_start();

		include_once __DIR__ . '/result-default-content.php';

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * @return TVA_Assessment|null
	 */
	public static function get_demo_assessment() {
		return current( TVA_Manager::get_demo_posts( [ 'post_type' => TVA_Const::ASSESSMENT_POST_TYPE ] ) );
	}

	/**
	 * Returns the demo result items
	 * If no items are found, it creates 3 result items
	 *
	 * @return TVA_User_Assessment[]
	 * @throws Exception
	 */
	public static function get_demo_items() {

		$items = array_map( static function ( $post ) {
			return new TVA_User_Assessment( $post );
		}, TVA_Manager::get_demo_posts( [
			'post_type'   => TVA_User_Assessment::POST_TYPE,
			'post_status' => 'any',
		] ) );

		if ( empty( $items ) ) {
			$assessment_demo_post = static::get_demo_assessment();

			if ( $assessment_demo_post instanceof WP_Post ) {
				foreach ( range( 1, 3 ) as $number ) {
					$user_assessment_demo = new TVA_User_Assessment( [
						'post_parent' => $assessment_demo_post->ID,
						'post_title'  => "Demo User Assessment $number",
					] );

					$user_assessment_demo->create();

					update_post_meta( $user_assessment_demo->ID, 'tva_is_demo', 1 );

					$items[] = $user_assessment_demo;
				}
			}
		}

		return $items;
	}

	/**
	 * Return the not graded text.
	 * Used in the result list element when the assessment is not graded yet
	 *
	 * @return string
	 */
	public static function get_not_graded_text() {
		return tcb_tva_dynamic_actions()->get_course_structure_label( 'assessment_not_graded', 'singular' );
	}

	/**
	 * Return the text that should show when no notes are available
	 *
	 * @return string
	 */
	public static function get_no_notes_text() {
		return '';
	}
}
