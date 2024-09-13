<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\UserTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Has_Post_Type {

	/**
	 * @var string[]
	 */
	public static $meta_key_list = [ 'name', 'content', 'type', 'css', 'media_css', 'thumb' ];

	/**
	 * @return string
	 */
	public static function get_post_type_name() {
		/**
		 * Allow other plugins to save templates under a different post type
		 *
		 * Used in ThriveApprentice when saving Certificate template
		 *
		 * @param string $post_type
		 */
		return apply_filters( 'tcb_user_templates_get_post_type_name', 'tve_user_template' );
	}

	/**
	 * @return string
	 */
	public static function get_post_type_prefix() {
		return 'template_';
	}

	/**
	 * @return string|void
	 */
	public static function get_post_title() {
		return __( 'User template', 'thrive-cb' );
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

		$category_id = wp_get_post_terms( $this->ID, static::get_taxonomy_name(), [ 'fields' => 'ids' ] );
		$post_data   = [
			'id'          => $this->ID,
			'is_migrated' => get_post_meta( $this->ID, 'is_migrated', true ),
			'id_category' => empty( $category_id ) || ! is_array( $category_id ) ? '' : $category_id[0],
			'is_imported' => get_post_meta( $this->ID, 'tve_kit_imported', true ) //whether or not the template is from ImportContent/Design Pack
		];

		foreach ( static::$meta_key_list as $meta_key ) {
			$post_data[ $meta_key ] = get_post_meta( $this->ID, static::get_post_type_prefix() . $meta_key, true );
		}

		return $post_data;
	}

	/**
	 * @param array $data
	 * @param bool  $is_migrated
	 *
	 * @return int|\WP_Error
	 */
	public static function insert_post( $data, $is_migrated = false ) {
		$meta_input = [];

		foreach ( static::$meta_key_list as $meta_key ) {
			$meta_input[ static::get_post_type_prefix() . $meta_key ] = $data[ $meta_key ];
		}

		if ( $is_migrated ) {
			$meta_input['is_migrated'] = 1;
		}

		$post_id = wp_insert_post( [
			'post_title'  => static::get_post_title(),
			'post_type'   => static::get_post_type_name(),
			'post_status' => 'publish',
			'meta_input'  => $meta_input,
		] );

		$category_id = empty( $data['id_category'] ) ? '' : $data['id_category'];

		wp_set_object_terms( $post_id, Category::normalize_category_id( $category_id ), Category::get_taxonomy_name() );

		return $post_id;
	}

	/**
	 * @param $data
	 */
	public function update_post( $data ) {
		foreach ( static::$meta_key_list as $meta_key ) {
			if ( isset( $data[ $meta_key ] ) ) {
				update_post_meta( (int) $this->ID, static::get_post_type_prefix() . $meta_key, $data[ $meta_key ] );
			}
		}

		if ( isset( $data['id_category'] ) ) {
			wp_set_object_terms( (int) $this->ID, Category::normalize_category_id( $data['id_category'] ), Category::get_taxonomy_name() );
		}
	}

	/**
	 * @param $name
	 */
	public function rename_post( $name ) {
		update_post_meta( $this->ID, static::get_post_type_prefix() . 'name', $name );
	}

	public function delete_post() {
		wp_delete_post( (int) $this->ID, true );
	}
}
