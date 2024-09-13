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

class Post_Date_Data_Field extends Data_Field {

	public static function get_id() {
		return 'post_date';
	}

	public static function get_supported_filters() {
		return [ 'time_date' ];
	}

	public static function get_name() {
		return __( 'Post date', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Filter by post date', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_DATE;
	}

	public static function get_dummy_value() {
		return '2021-09-06 17:18:57';
	}
}
