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

class Assessment_Grade extends Field {
	public static function get_entity() {
		return Assessment::get_key();
	}

	public function get_value( $assessment ) {
		$grade = '';
		if ( $assessment ) {
			$user_assessments = TVA_User_Assessment::get_user_submission( [
				'post_parent' => $assessment->ID,
				'meta_query'  => [
					[
						'key'     => TVA_User_Assessment::GRADE_META,
						'compare' => 'EXISTS',
					],
					[
						'key'     => TVA_User_Assessment::STATUS_META,
						'value'   => TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT,
						'compare' => '!=',
					],
					'relation' => 'AND',
				],
			] );

			if ( ! empty( $user_assessments ) ) {
				$user_assessment = $user_assessments[0];
				$grade           = $user_assessment->get_grade( true );
			}
		}

		return $grade;
	}

	public static function get_key() {
		return 'assessment_grade';
	}

	public static function get_label() {
		return __( 'Assessment grade', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'string_equals' ];
	}

	public static function get_placeholder_text() {
		return __( 'Enter assessment grade', 'thrive-apprentice' );
	}
}
