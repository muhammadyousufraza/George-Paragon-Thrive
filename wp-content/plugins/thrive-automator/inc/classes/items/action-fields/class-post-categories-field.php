<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Categories_Field extends Action_Field {
	public static function get_name() {
		return __( 'Post category', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'WordPress post category', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Post category', 'thrive-automator' );
	}

	public static function get_id() {
		return 'post_categories';
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_TAGS;
	}

	public static function get_preview_template() {
		return __( 'Categories:', 'thrive-automator' ) . ' $$value';
	}

	public static function get_validators() {
		return [ 'required' ];
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
			'message' => __( 'Type a category and press Enter. Use a comma to use multiple categories', 'thrive-automator' ),
		];
	}
}
