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

class Comment_Content_Data_Field extends Data_Field {

	public static function get_id() {
		return 'comment_content';
	}

	public static function get_supported_filters() {
		return [ 'string_ec' ];
	}

	public static function get_name() {
		return __( 'Comment content', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Target those that have posted a comment that includes a certain target word ', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return __( 'This is an example comment', 'thrive-automator' );
	}
}
