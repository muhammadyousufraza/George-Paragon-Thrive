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

class Comment_Type_Data_Field extends Data_Field {

	public static function get_id() {
		return 'comment_type';
	}

	public static function get_supported_filters() {
		return [ 'checkbox' ];
	}

	public static function get_name() {
		return __( 'Comment type', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Target by the comment type (comment, pingback, trackback)', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_options_callback() {
		return [
			'comment'   => [
				'id'    => 'comment',
				'label' => __( 'Comment', 'thrive-automator' ),
			],
			'pingback'  => [
				'id'    => 'pingback',
				'label' => __( 'Pingback', 'thrive-automator' ),
			],
			'trackback' => [
				'id'    => 'trackback',
				'label' => __( 'Trackback', 'thrive-automator' ),
			],
		];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'comment';
	}
}
