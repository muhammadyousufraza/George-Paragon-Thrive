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

class Assessment_Available extends Field {
	public static function get_entity() {
		return Assessment::get_key();
	}

	public function get_value( $object ) {
		$counter = 0;
		if ( $object ) {
			$counter = (int) get_user_meta( get_current_user_id(), TVA_User_Assessment::SUBMISSION_COUNTER_META . $object->ID, true );
		}

		return $counter > 0;
	}

	public static function get_key() {
		return 'assessment_available';
	}

	public static function get_label() {
		return __( 'Assessment available', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [];
	}

	public static function is_boolean() {
		return true;
	}

	public static function get_display_order() {
		return 0;
	}
}
