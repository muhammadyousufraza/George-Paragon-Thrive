<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\VideoReporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Has_Post_Type {
	/**
	 * @var string[]
	 */
	public static $meta_key_list = [ 'url', 'title', 'provider', 'percentage_to_complete', 'duration' ];

	/**
	 * @return string
	 */
	public static function get_post_type_name() {
		return 'tve_video_data';
	}

	/**
	 * @return string|void
	 */
	public static function get_post_title() {
		return __( 'Video reporting', 'thrive-cb' );
	}

	public static function register_post_type() {
		register_post_type( static::get_post_type_name(), [
			'public'              => false,
			'publicly_queryable'  => is_user_logged_in(),
			'query_var'           => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'_edit_link'          => 'post.php?post=%d',
			'map_meta_cap'        => true,
			'label'               => static::get_post_title(),
			'capabilities'        => [
				'edit_others_posts'    => 'tve-edit-cpt',
				'edit_published_posts' => 'tve-edit-cpt',
			],
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'has_archive'         => false,
		] );
	}

	/**
	 * @param $video_url
	 *
	 * @return int[]|\WP_Post
	 */
	public static function get_post_id_by_video_url( $video_url ) {
		if ( empty( $video_url ) ) {
			return null;
		}

		$videos = get_posts( [
			'posts_per_page' => - 1,
			'post_type'      => static::get_post_type_name(),
			'meta_key'       => 'url',
			'meta_value'     => $video_url,
			'fields'         => 'ids',
		] );

		return empty( $videos ) ? null : $videos[0];
	}

	/**
	 * @param array $data
	 *
	 * @return int|\WP_Error
	 */
	public static function insert_post( $data ) {
		$meta_input = [];

		foreach ( static::$meta_key_list as $meta_key ) {
			$meta_input[ $meta_key ] = $data[ $meta_key ];
		}

		return wp_insert_post( [
			'post_title'  => static::get_post_title(),
			'post_type'   => static::get_post_type_name(),
			'post_status' => 'publish',
			'meta_input'  => $meta_input,
		] );
	}

	/**
	 * @param $data
	 */
	public function update_post( $data ) {
		foreach ( static::$meta_key_list as $meta_key ) {
			if ( isset( $data[ $meta_key ] ) ) {
				update_post_meta( (int) $this->ID, $meta_key, $data[ $meta_key ] );
			}
		}
	}

	public function get_title() {
		return get_post_meta( (int) $this->ID, 'title', true );
	}

	public function get_full_duration() {
		return get_post_meta( (int) $this->ID, 'duration', true );
	}

	public function get_percentage_to_complete() {
		return get_post_meta( (int) $this->ID, 'percentage_to_complete', true );
	}
}
