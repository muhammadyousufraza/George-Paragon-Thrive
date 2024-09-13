<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Fields;

use TCB\ConditionalDisplay\Field;
use TVA\Architect\ConditionalDisplay\Entities\Assessment;
use TVA\Assessments\TVA_User_Assessment;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Assessment_Status extends Field {
	public static function get_entity() {
		return Assessment::get_key();
	}

	public function get_value( $assessment ) {
		$status = '';

		if ( $assessment ) {
			$user_assessments = TVA_User_Assessment::get_user_submission( [
				'post_parent' => $assessment->ID,
				'meta_query'  => [
					[
						'key'     => TVA_User_Assessment::OUTDATED_META,
						'compare' => 'NOT EXISTS',
					],
				],
			] );

			if ( ! empty( $user_assessments ) ) {
				$status = get_post_meta( $user_assessments[0]->ID, TVA_User_Assessment::STATUS_META, true );
			}
		}

		return $status;
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		return [
			[
				'value' => TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT,
				'label' => __( 'Pending assessment', 'thrive-apprentice' ),
			],
			[
				'value' => TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED,
				'label' => __( 'Completed - passed', 'thrive-apprentice' ),
			],
			[
				'value' => TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED,
				'label' => __( 'Completed - failed', 'thrive-apprentice' ),
			],
		];
	}

	public static function get_key() {
		return 'assessment_status';
	}

	public static function get_label() {
		return __( 'Assessment status', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}
}
