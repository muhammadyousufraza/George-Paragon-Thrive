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

class Comment_Author_IP_Data_Field extends Data_Field {

	public static function get_id() {
		return 'comment_author_IP';
	}

	public static function get_supported_filters() {
		return [ 'string_ec' ];
	}

	public static function get_name() {
		return __( 'Comment author IP address in IPv4 format', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Target by comment author\'s IP address', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return '26.103.134.169';
	}
}
