<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Author_Display_Name_Field extends Data_Field {

	public static function get_id() {
		return 'post_author_display_name';
	}

	public static function get_name() {
		return __( 'Post author display name', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Filter by post author display name', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_supported_filters() {
		return [ 'string_equals' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'John Doe';
	}
}
