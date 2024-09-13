<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Post_Id_Field
 */
class Post_Id_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'Post ID', 'thrive-automator' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Filter by WordPress post id', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'wp_post_id';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return '1';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_options_callback() {
		$posts = [];
		foreach (
			get_posts( [
				'posts_per_page' => '-1',
				'post_type'      => get_post_types( '', 'names' ),
			] ) as $post
		) {
			$posts[ $post->ID ] = [
				'label' => $post->post_title,
				'id'    => $post->ID,
			];
		}

		return $posts;
	}

	public static function primary_key() {
		return Post_Data::get_id();
	}
}
