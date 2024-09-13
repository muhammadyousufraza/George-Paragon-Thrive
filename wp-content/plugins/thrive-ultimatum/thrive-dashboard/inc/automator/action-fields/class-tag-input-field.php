<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Tag_Input_Field
 */
class Tag_Input_Field extends Action_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Tags';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Which tag(s) would you like to apply?  Use a comma to add multiple tags. Example:- tag1, tag2';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return 'Send tags: $$value';
	}

	public static function get_id() {
		return 'tag_input';
	}

	public static function get_type() {
		return 'tags';
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function allow_dynamic_data() {
		return true;
	}

	/**
	 * An array of extra options to be passed to the field which can affect the display of the field
	 *
	 * @return array
	 */
	public static function get_extra_options() {
		return [
			'message' => 'Type a tag and press Enter. Use a comma to add multiple tags',
		];
	}
}
