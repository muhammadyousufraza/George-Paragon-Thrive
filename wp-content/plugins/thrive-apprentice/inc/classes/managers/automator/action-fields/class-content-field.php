<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Content_Field
 */
class Content_Field extends Action_Field {

	public static function get_id() {
		return 'content';
	}

	public static function get_type() {
		return 'autocomplete_toggle';
	}

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Choose which content to unlock:';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'User can add one or more pieces of content to be unlocked by title.';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Choose content';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Content(s): $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$values = array();

		if ( property_exists( $action_data, 'content_type' ) ) {
			$content_type = $action_data->content_type->value;

			if ( $content_type === TVA_Const::LESSON_POST_TYPE ) {
				$values = Main::get_lessons();
			} elseif ( $content_type === TVA_Const::MODULE_POST_TYPE ) {
				$values = Main::get_modules();
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

	public static function allowed_data_set_values() {
		return [ Module_Data::get_id(), Lesson_Data::get_id() ];
	}
}
