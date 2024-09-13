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

class Post_Type_Data_Field extends Data_Field {

	public static function get_id() {
		return 'post_type';
	}

	public static function get_name() {
		return __( 'Post type', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Filter by post type', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'post';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$post_types = [];

		foreach ( get_post_types( array( 'public' => true ) ) as $key => $post_type ) {
			$post_types[ $key ] = [
				'label' => $post_type,
				'id'    => $key,
			];
		}

		return $post_types;
	}

	public static function is_ajax_field() {
		return true;
	}
}
