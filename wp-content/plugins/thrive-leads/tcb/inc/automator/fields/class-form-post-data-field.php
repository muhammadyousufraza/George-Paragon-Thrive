<?php

namespace TCB\Integrations\Automator;

use TCB\inc\helpers\FormSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Post_Field
 */
class Form_Post_Data_Field extends \Thrive\Automator\Items\Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Page or post containing form';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Select pages or posts on your website that contain forms';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Filter by post';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$lg_posts = new \WP_Query( [
			'post_type'      => FormSettings::POST_TYPE,
			'fields'         => 'id=>parent',
			'posts_per_page' => '-1',
			'post_status'    => 'draft',
		] );
		$posts    = [];
		/**
		 * Prevent showing LG from some post types
		 */
		$banned_post_types = apply_filters( 'tve_aut_banned_post_types', [] );
		foreach ( $lg_posts->posts as $lg_post ) {
			$parent_post = get_post( $lg_post->post_parent );
			if ( ! empty( $parent_post ) && ! in_array( $parent_post->post_type, $banned_post_types, true ) ) {
				$posts[ $parent_post->ID ] = [
					'label' => $parent_post->post_title,
					'id'    => $parent_post->ID,
				];
			}
		}

		return $posts;
	}


	public static function get_id() {
		return 'post_id';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_dummy_value() {
		return '10';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
