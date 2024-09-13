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

trait Has_Taxonomy {
	/**
	 * @return string
	 */
	public static function get_taxonomy_name() {
		return 'tve_user_template_category';
	}

	/**
	 * @return string
	 */
	public static function get_taxonomy_meta_key_prefix() {
		return 'template_category_';
	}

	/**
	 * @return string|void
	 */
	public static function get_taxonomy_title() {
		return __( 'User template category', 'thrive-cb' );
	}

	public static function register_taxonomy() {
		register_taxonomy( static::get_taxonomy_name(), [ Template::get_post_type_name() ], [
			'hierarchical'      => false,
			'show_ui'           => false,
			'show_in_nav_menus' => false,
			'show_admin_column' => false,
			'query_var'         => false,
			'show_in_rest'      => false,
			'public'            => false,
		] );

		register_taxonomy_for_object_type( static::get_taxonomy_name(), Template::get_post_type_name() );
	}

	/**
	 * @return int[]|string|string[]|\WP_Error|\WP_Term[]
	 */
	public static function get_terms() {
		return get_terms( [
			'taxonomy'   => static::get_taxonomy_name(),
			'orderby'    => 'id',
			'hide_empty' => 0,
		] );
	}

	/**
	 * @param $name
	 *
	 * @return array|int[]|\WP_Error
	 */
	public static function insert_term( $name ) {
		return wp_insert_term( $name, static::get_taxonomy_name() );
	}

	/**
	 * @param $name
	 */
	public function rename_term( $name ) {
		wp_update_term( $this->ID, static::get_taxonomy_name(), [ 'name' => $name ] );
	}

	public function delete_term() {
		wp_delete_term( $this->ID, static::get_taxonomy_name() );
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_meta( $key ) {
		return get_term_meta( $this->ID, $key, true );
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function update_meta( $key, $value ) {
		update_term_meta( $this->ID, $key, $value );
	}
}
