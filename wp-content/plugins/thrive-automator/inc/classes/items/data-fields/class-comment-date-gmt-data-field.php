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

class Comment_Date_Gmt_Data_Field extends Data_Field {

	public static function get_id() {
		return 'comment_date_gmt';
	}

	public static function get_supported_filters() {
		return [ 'time_date' ];
	}

	public static function get_name() {
		return __( 'Comment date in GMT zone', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Target by the date in GMT timezone when the comment was submitted', 'thrive-automator' );
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
