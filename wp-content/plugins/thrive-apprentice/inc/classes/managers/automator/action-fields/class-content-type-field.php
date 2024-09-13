<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Content_Type_Field
 */
class Content_Type_Field extends Action_Field {

	public static function get_id() {
		return 'content_type';
	}

	public static function get_type() {
		return 'select';
	}

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Which type of content would you like to unlock:';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Users can choose from any of the content types on the site';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Choose content type';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Content type: $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		return [
			[
				'id'    => TVA_Const::MODULE_POST_TYPE,
				'label' => TVA_Const::TVA_COURSE_MODULE_TEXT,
			],
			[
				'id'    => TVA_Const::LESSON_POST_TYPE,
				'label' => TVA_Const::TVA_COURSE_LESSON_TEXT,
			],
		];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
