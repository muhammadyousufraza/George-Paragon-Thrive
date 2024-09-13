<?php

namespace TVA\Automator;


use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\Automation_Data;
use Thrive\Automator\Utils;
use TVA\Assessments\Grading\Base as Grading;
use TVA\Assessments\Grading\Category;
use TVA\Assessments\Grading\PassFail;
use TVA\Assessments\Grading\Percentage;
use TVA\Assessments\Grading\Score;
use TVA\Assessments\TVA_User_Assessment;
use TVA_Course_V2;
use TVA_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Grade_Assessment extends Action {

	public static function get_id() {
		return 'thrive/grade_assessment';
	}

	public static function get_name() {
		return 'Grade assessment';
	}

	public static function get_description() {
		return 'Choose an assessment to grade';
	}

	public static function get_image() {
		return 'tap-product-enroll';
	}

	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	public static function get_required_action_fields() {
		return array(
			Grade_Assessment_Field::get_id(),
			'grade_automatically' => array(
				Choose_Grade_Field::get_id(),
				Set_Grade_Field::get_id(),
				Integer_Grade_Field::get_id(),
			),
		);
	}

	public static function get_required_data_objects() {
		return [ 'user_data' ];
	}

	/**
	 * Grade an assessment based if enough data is available
	 *
	 * @param Automation_Data $data
	 *
	 * @return void
	 */
	public function do_action( $data ) {
		$assessment_id = $this->get_assessment_id( $data['assessment_data'] );

		if ( ! $assessment_id ) {
			return;
		}

		$user_assessment = static::get_user_assessment(
			$data['user_assessment'],
			$assessment_id,
			$data['user_data']
		);

		if ( ! $user_assessment ) {
			return;
		}

		$grading_details = Grading::get_assessment_grading_details( $assessment_id );
		$grade_value     = $this->get_grade_value( $grading_details );

		if ( ! empty( $grade_value ) ) {
			$user_assessment->save_grade( $grade_value, '' );
		}
	}

	public static function get_subfields( $subfields, $current_value, $action_data ) {
		$subfield      = $subfields[0];
		$subfield_data = [];
		$assessment_id = $action_data->assessment->value;

		if ( ! is_numeric( $assessment_id ) ) {
			$subfield = $subfields[1];
		} else {
			$grading_details = Grading::get_assessment_grading_details( $assessment_id );

			if ( $grading_details['grading_method'] !== 'pass_fail' && $grading_details['grading_method'] !== 'category' ) {
				$subfield = $subfields[2];
			}
		}

		if ( ! $current_value ) {
			$subfield_data[] = parent::get_subfields( $subfields, $current_value, $action_data )[ $subfield ];
		}

		return $subfield_data;
	}

	/**
	 * @param $data Assessment_Data The available automation data
	 *
	 * @return int The id of the course assessment we are trying to grade
	 * or 0 if a course assessment wasn't provided
	 */
	private function get_assessment_id( $data ) {
		$assessment_id = 0;

		if ( is_numeric( $this->data['assessment']['value'] ) ) {
			$assessment_id = $this->data['assessment']['value'];
		} elseif ( $data ) {
			$assessment_id = $data->get_value( 'assessment_id' );
		}

		return $assessment_id;
	}

	/**
	 * @param User_Assessment_Data $user_assessment_data
	 * @param int $assessment_id
	 * @param User_Data $user_data
	 *
	 * @return TVA_User_Assessment|false The user assessment.
	 * False if it can't be identified
	 */
	private static function get_user_assessment( $user_assessment_data, $assessment_id, $user_data ) {
		if ( $user_assessment_data ) {
			$user_assessment = new TVA_User_Assessment( get_post( $user_assessment_data->get_value( 'user_assessment_id' ) ) );
		} else {
			$user_assessment = TVA_User_Assessment::get_user_submission( [
				'post_parent'    => $assessment_id,
				'posts_per_page' => 1,
				'author'         => $user_data->get_value( 'user_id' ),
				'meta_query'     => array(
					'key'     => TVA_User_Assessment::STATUS_META,
					'value'   => \TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT,
					'compare' => '=',
				),
			] );

			if ( ! empty( $user_assessment[0] ) ) {
				$user_assessment = $user_assessment[0];
			}
		}

		return $user_assessment;
	}

	/**
	 * @param array $grading_details The grading details for which the grade will be calculated
	 *
	 * @return int|string The value of the grade
	 * Empty string if the grade couldn't be found/calculated
	 */
	private function get_grade_value( $grading_details ) {

		if ( ! empty ( $this->data['grade_automatically']['value'] ) ) {
			$grading_instance = Grading::factory( $grading_details );
			$grade_value      = $grading_instance->get_passing_grade();
		} else {
			switch ( $grading_details['grading_method'] ) {
				case Grading::CATEGORY_METHOD:
					$grade_value = $this->data['grade_automatically']['subfield']['choose_grade']['value'];
					$categories  = array_merge(
						$grading_details['grading_method_data'][ Category::PASS_META ],
						$grading_details['grading_method_data'][ Category::FAIL_META ]
					);

					/** Try to find the category id when grade is selected manually form input */
					foreach ( $categories as $category ) {
						if ( $category['name'] === $grade_value ) {
							$grade_value = $category['ID'];
						}
					}
					break;
				case Grading::PASS_FAIL_METHOD:
					$grade_value = $this->data['grade_automatically']['subfield']['choose_grade']['value'];
					break;
				case Grading::PERCENTAGE_METHOD:
				case Grading::SCORE_METHOD:
					$grade_value = $this->data['grade_automatically']['subfield']['integer_grade']['value'];
					break;
				default:
					$grade_value = $this->data['grade_automatically']['subfield']['set_grade']['value'];
			}
		}

		return $grade_value;
	}
}
