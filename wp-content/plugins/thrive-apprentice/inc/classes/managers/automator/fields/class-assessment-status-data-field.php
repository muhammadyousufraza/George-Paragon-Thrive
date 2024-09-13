<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Assessment_Status_Data_Field
 */
class Assessment_Status_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Assessment Status';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Target an assessment its status';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'assessment_status';
	}

	public static function get_supported_filters() {
		return [ 'dropdown' ];
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		return [
			[
				'id'    => TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT,
				'label' => TVA_Const::ASSESSMENTS_PENDING_TEXT,
			],
			[
				'id'    => TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED,
				'label' => TVA_Const::ASSESSMENTS_PASSED_TEXT,
			],
			[
				'id'    => TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED,
				'label' => TVA_Const::ASSESSMENTS_FAILED_TEXT,
			],
		];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 9;
	}
}
