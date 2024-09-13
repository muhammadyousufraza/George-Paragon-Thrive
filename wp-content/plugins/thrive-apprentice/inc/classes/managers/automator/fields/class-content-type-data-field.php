<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Const;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Content_Type_Field
 */
class Content_Type_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Content type';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by content type unlocked';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
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

	public static function get_id() {
		return 'content_type';
	}

	public static function get_supported_filters() {
		return [ 'dropdown' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'content-type-value';
	}
}
