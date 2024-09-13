<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Assessments\Grading\Base as Grading_Base;
use TVA_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class User_Assessment_Data
 */
class User_Assessment_Data extends Data_Object {
	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'user_assessment_data';
	}

	public static function get_nice_name() {
		return 'User Assessment Data ID';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'assessment_author_display_name',
			'assessment_author_email',
			'assessment_status',
			'assessment_mark',
		];
	}

	/**
	 * Create user assessment data object from $param
	 *
	 * @param $param TVA_User_Assessment|array|int The data about the assessment
	 *
	 * @return array|null
	 */
	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Product_Data object' );
		}

		$user_assessment = null;

		if ( $param instanceof TVA_User_Assessment ) {
			$user_assessment = $param;
		} elseif ( is_numeric( $param ) ) {
			$user_assessment = new TVA_User_Assessment( get_post( (int) $param ) );
		}

		$author = new \WP_User( $user_assessment->post_author );

		if ( $user_assessment ) {
			return [
				User_Assessment_ID_Data_Field::get_id() => $user_assessment->ID,
				Assessment_Author_Display_Name_Data_Field::get_id() => $author->display_name,
				Assessment_Author_Email_Data_Field::get_id() => $author->user_email,
				Assessment_Status_Data_Field::get_id()  => $user_assessment->status,
				Assessment_Mark_Data_Field::get_id()    => $user_assessment->grade,
			];
		}

		return null;
	}
}
