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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Assessment_Count extends Field {
	public static function get_entity() {
		return Assessment::get_key();
	}

	public function get_value( $assessment ) {
		return $assessment ? get_user_meta( get_current_user_id(), TVA_User_Assessment::SUBMISSION_COUNTER_META . $assessment->ID, true ) : 0;
	}

	public static function get_key() {
		return 'assessment_count';
	}

	public static function get_label() {
		return __( 'Assessment count', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'number_comparison' ];
	}
}
