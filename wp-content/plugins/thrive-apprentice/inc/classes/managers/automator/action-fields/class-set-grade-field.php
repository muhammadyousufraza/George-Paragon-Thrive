<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Set_Grade_Field
 */
class Set_Grade_Field extends Action_Field {

	public static function get_id() {
		return 'set_grade';
	}

	public static function get_type() {
		return 'text';
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
		return 'Set a grade';
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

	public static function allow_dynamic_data() {
		return true;
	}
}
