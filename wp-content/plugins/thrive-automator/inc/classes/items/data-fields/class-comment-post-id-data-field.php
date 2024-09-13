<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use function get_posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Comment_Post_ID_Data_Field extends Data_Field {

	public static function get_id() {
		return 'comment_post_ID';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_name() {
		return __( 'Comment post ID', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Target a specific set of posts/pages on the user\'s site', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_options_callback() {
		$posts = [];
		foreach (
			get_posts( [
				'posts_per_page' => '-1',
				'comment_status' => 'open',
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

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return '22';
	}
}
