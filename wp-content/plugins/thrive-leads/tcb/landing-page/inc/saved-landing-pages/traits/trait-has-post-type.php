<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\SavedLandingPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Has_Post_Type {
	public static function get_post_type_name() {
		return 'tve_saved_lp';
	}

	public static function get_post_type_prefix() {
		return 'lp_';
	}

	/**
	 * @return string|void
	 */
	public static function get_post_title() {
		return __( 'Saved Landing Page', 'thrive-cb' );
	}

	public static function register_post_type() {
		register_post_type( static::get_post_type_name(), [
			'public'              => isset( $_GET[ TVE_EDITOR_FLAG ] ),
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
	 * @param array $args
	 *
	 * @return int[]|\WP_Post[]
	 */
	public static function get_posts( $args = [] ) {
		$default_args = [
			'post_type'      => static::get_post_type_name(),
			'posts_per_page' => - 1,
			'order'          => 'ASC',
		];

		return get_posts( array_merge( $default_args, $args ) );
	}

	/**
	 * @return array
	 */
	public function get_post_data() {
		if ( empty( get_post( $this->ID ) ) ) {
			return [];
		}

		$keys = array_merge( static::get_meta_keys(), static::get_content_keys() );

		$data = [
			'id'          => $this->ID,
			'is_migrated' => get_post_meta( $this->ID, 'is_migrated', true ),
		];

		foreach ( $keys as $meta_key ) {
			$data[ $meta_key ] = get_post_meta( $this->ID, static::get_post_type_prefix() . $meta_key, true );
		}

		return $data;
	}

	public function get_localized_post_data() {
		$keys = static::get_localize_keys();

		$data = [
			'id'           => $this->ID,
			'is_migrated'  => get_post_meta( $this->ID, 'is_migrated', true ),
			'is_from_pack' => get_post_meta( $this->ID, 'tve_kit_imported', true ), //whether the template is from Design Pack or not
		];

		foreach ( $keys as $meta_key ) {
			$value = get_post_meta( $this->ID, static::get_post_type_prefix() . $meta_key, true );

			if ( isset( $value ) ) {
				$data[ $meta_key ] = $value;
			}
		}

		if ( empty( $data['preview_image']['url'] ) && empty( $data['thumbnail'] ) ) {
			$data['preview_image'] = \TCB_Utils::get_placeholder_data();
		}

		return $data;
	}


	/**
	 * @param array $data
	 * @param bool  $is_migrated
	 *
	 * @return int|\WP_Error
	 */
	public static function insert_post( $data, $is_migrated = false ) {
		$meta_input = [];

		foreach ( $data as $key => $value ) {
			$meta_input[ static::get_post_type_prefix() . $key ] = $value;
		}

		if ( $is_migrated ) {
			$meta_input['is_migrated'] = 1;
		}


		return wp_insert_post( [
			'post_title'  => $data['name'],
			'post_type'   => static::get_post_type_name(),
			'post_status' => 'publish',
			'meta_input'  => $meta_input,
		] );
	}


	public function remove_post() {
		wp_delete_post( (int) $this->ID, true );
	}

	public static function get_meta_keys() {
		return [
			'name',
			'template',
			'tags',
			'date',
			'imported',
			'zip_filesize',
			'thumbnail',
			'preview_image',
			'theme_dependency',
			'tpl_colours',
			'tpl_gradients',
			'tpl_button',
			'tpl_section',
			'tpl_contentbox',
			'tpl_palettes',
			'tpl_palettes_v2',
			'tpl_palettes_config_v2',
			'tpl_skin_tag',
		];
	}

	/**
	 * This is the only data that we need at localize
	 *
	 * @return string[]
	 */
	public static function get_localize_keys() {
		return [
			'name',
			'template',
			'tags',
			'date',
			'imported',
			'zip_filesize',
			'thumbnail',
			'preview_image',
			'theme_dependency',
			'tpl_skin_tag',
		];
	}

	public static function get_content_keys() {
		return [
			'content',
			'inline_css',
			'custom_css',
			'tve_globals',
			'tve_global_script',
			'sections',
		];
	}
}
