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

class Post_Status_Data_Field extends Data_Field {

	public static function get_id() {
		return 'post_status';
	}

	public static function get_supported_filters() {
		return [ 'string_equals' ];
	}

	public static function get_name() {
		return __( 'Post status', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Filter by post status', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'published';
	}
}
