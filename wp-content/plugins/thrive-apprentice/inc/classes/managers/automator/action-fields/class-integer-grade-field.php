<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;
use TVA\Assessments\Grading\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Integer_Grade_Field
 */
class Integer_Grade_Field extends Action_Field {

	public static function get_id() {
		return 'integer_grade';
	}

	public static function get_type() {
		return 'number';
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
		return 'Set a score/percentage';
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

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
