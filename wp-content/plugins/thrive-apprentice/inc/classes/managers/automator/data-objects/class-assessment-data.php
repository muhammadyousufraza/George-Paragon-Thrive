<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Assessments\Grading\Base as Grading_Base;
use TVA_Assessment;
use TVA_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Assessment_Data
 */
class Assessment_Data extends Data_Object {
	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'assessment_data';
	}

	public static function get_nice_name() {
		return 'Assessment Data ID';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			Assessment_ID_Data_Field::get_id(),
			Assessment_Type_Data_Field::get_id(),
			Assessment_Grading_Method_Data_Field::get_id(),
			Assessment_Link_Data_Field::get_id(),
			Assessment_Date_Data_Field::get_id(),
		];
	}

	/**
	 * Create assessment data object from $param
	 *
	 * @param $param TVA_Assessment|array|int The data about the assessment
	 *
	 * @return array|null
	 */
	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Product_Data object' );
		}

		$assessment = null;

		if ( $param instanceof \TVA_Assessment ) {
			$assessment = $param;
		} elseif ( is_numeric( $param ) ) {
			$assessment = new \TVA_Assessment( get_post( (int) $param ) );
		}

		$grading_method = Grading_Base::get_assessment_grading_details( $assessment->ID )['grading_method'];

		if ( $assessment ) {
			return [
				Assessment_ID_Data_Field::get_id()   => $assessment->ID,
				Assessment_Type_Data_Field::get_id() => $assessment->get_type(),
				Assessment_Grading_Method_Data_Field::get_id() => $grading_method,
				Assessment_Link_Data_Field::get_id() => $assessment->get_url(),
				Assessment_Date_Data_Field::get_id() => $assessment->post_date,
			];
		}

		return null;
	}

	public static function get_data_object_options() {
		$assessments = [];
		$filters     = [
			'meta_query' => [
				[
					'key'     => 'tva_is_demo',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		foreach ( TVA_Manager::get_assessments( $filters ) as $assessment ) {
			$assessments[ $assessment->ID ] = [
				'label' => $assessment->post_title,
				'id'    => $assessment->ID,
			];
		}

		return $assessments;
	}
}
