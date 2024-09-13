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

class Post_Url_Data_Field extends Data_Field {

	public static function get_id() {
		return 'post_url';
	}

	public static function get_supported_filters() {
		return [ 'string_ec' ];
	}

	public static function get_name() {
		return __( 'Post URL', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Filter by post url', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'https://wordpress.org';
	}
}
