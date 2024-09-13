<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Difficulty_Field
 */
class Assessment_Checkbox_Field extends Action_Field {
	public static function get_id() {
		return 'grade_automatically';
	}

	public static function get_type() {
		return 'checkbox';
	}

	/**
	 * Field name
	 */
	public static function get_name() {
		return '';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'some description';
	}
	public static function get_default_value() {
		return 1;
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'some placeholder';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Graded Automatically';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		return [
			[
				'id'    => 1,
				'label' => 'Calculate passing grade automatically',
			],
		];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [];
	}

	public static function allowed_data_set_values() {
		return [];
	}
}
