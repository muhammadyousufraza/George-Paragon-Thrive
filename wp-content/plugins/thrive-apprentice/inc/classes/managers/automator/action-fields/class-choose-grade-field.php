<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;
use TVA\Assessments\Grading\Base;
use TVA\Assessments\Grading\Base as Grading_Base;
use TVA\Assessments\Grading\Category;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Content_Type_Field
 */
class Choose_Grade_Field extends Action_Field {

	public static function get_id() {
		return 'choose_grade';
	}

	public static function get_type() {
		return 'select';
	}

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Grade';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Grade';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Select a grade';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Grade : $$value';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		$values        = [];
		$assessment_id = (int) $action_data->assessment->value;

		if ( ! empty( $action_data ) && property_exists( $action_data, 'assessment' ) && ! empty( $assessment_id ) ) {
			$grading_details = Base::get_assessment_grading_details( $assessment_id );

			switch ( $grading_details['grading_method'] ) {
				case Grading_Base::PASS_FAIL_METHOD:
					$values[] = [
						'id'    => 'pass',
						'label' => 'Pass',
					];
					$values[] = [
						'id'    => 'fail',
						'label' => 'Fail',
					];
					break;
				case Grading_Base::CATEGORY_METHOD:
					$categories = array_merge(
						$grading_details['grading_method_data'][ Category::PASS_META ],
						$grading_details['grading_method_data'][ Category::FAIL_META ]
					);

					foreach ( $categories as $category ) {
						$values[] = [
							'id'    => $category['ID'],
							'label' => $category['name'],
						];
					}

					break;
				default:
					$values = [];
			}
		}

		return $values;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
