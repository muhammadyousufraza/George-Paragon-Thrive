<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Menu_Settings {

	const POST_TYPE = 'tve_menu_settings';

	/**
	 * @var WP_Post
	 */
	private $post;

	public function __construct( $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			$post = get_post( $post );
		}

		$this->post = $post;
	}

	public function update( $data ) {
		$data = array_merge( [
			'id'     => '',
			'config' => [],
			'before' => 0,
			'after'  => 0,
		], $data );

		$this->update_meta( '_menu_id', $data['id'] );
		$this->update_meta( '_config', $data['config'] );

		$this->update_meta( '_before', $data['before'] );
		$this->update_meta( '_after', $data['after'] );
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return void
	 */
	private function update_meta( $key, $value ) {
		update_post_meta( $this->get_id(), $key, $value );
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	private function get_meta( $key ) {
		return get_post_meta( $this->get_id(), $key, true );
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return empty( $this->post ) ? 0 : $this->post->ID;
	}

	/**
	 * @return mixed
	 */
	public function get_config() {
		return $this->get_meta( '_config' );
	}

	/**
	 * Get before/after html for the hamburger menu
	 *
	 * @param string  $position
	 * @param boolean $raw
	 *
	 * @return mixed|string
	 */
	public function get_extra_html( $position, $raw = false ) {
		$html = $this->get_meta( $position );

		if ( ! $raw && is_numeric( $html ) ) {
			$html = '';
		}

		return do_shortcode( $html );
	}

	/**
	 * @return boolean
	 */
	public function has_custom_content_saved() {
		return ! is_numeric( $this->get_extra_html( '_before', true ) ) || ! is_numeric( $this->get_extra_html( '_after', true ) );
	}

	/**
	 * @param array $data
	 * @param int   $post_id
	 *
	 * @return static
	 */
	public static function save( $data = [], $post_id = 0 ) {
		$menu_settings = static::get_menu_settings_instance( $data['id'] ?? 0, $post_id );

		if ( $menu_settings === null ) {
			remove_all_filters( 'wp_insert_post_data' );
			remove_all_actions( 'edit_post' );
			remove_all_actions( 'save_post' );
			remove_all_actions( 'wp_insert_post' );

			$id = wp_insert_post( [
				'post_type'   => static::POST_TYPE,
				'post_title'  => "Settings for menu on $post_id",
				'post_parent' => $post_id,
				'post_status' => 'publish',
			] );

			$menu_settings = new static( $id );
		}

		$menu_settings->update( $data );

		return $menu_settings;
	}

	/**
	 * @param $menu_id
	 * @param $post_id
	 *
	 * @return static
	 */
	public static function get_menu_settings_instance( $menu_id, $post_id ) {
		$posts = get_posts( [
			'post_type'   => static::POST_TYPE,
			'post_parent' => $post_id,
			'meta_query'  => [
				[
					'key'   => '_menu_id',
					'value' => $menu_id,
				],
			],
		] );

		return empty( $posts ) ? null : new static( $posts[0] );
	}

	public static function init() {
		add_filter( 'tcb.content_pre_save', static function ( $response, $post_data ) {
			$post_id  = isset( $post_data['post_id'] ) ? (int) $post_data['post_id'] : 0;
			$menu_ids = [];

			if ( ! empty( $post_data['menus'] ) ) {
				foreach ( $post_data['menus'] as $menu_data ) {
					$menu_settings = static::save( $menu_data, $post_id );

					$menu_ids[] = $menu_settings->get_id();

					$response['menus'][ $menu_data['id'] ?? 0 ] = $menu_settings->get_id();
				}
			}

			// Symbols don't send their ID so there is a risc of removing unwanted menus TODO: fix this
			//static::remove_unused_menus( $post_id, $menu_ids );

			return $response;
		}, 10, 2 );
	}

	/**
	 * When saving a page, menus that were not sent have been removed from the page so we also remove them
	 *
	 * @param $post_id
	 * @param $saved_menus
	 *
	 * @return void
	 */
	public static function remove_unused_menus( $post_id, $saved_menus ) {
		$menus = get_posts( [
			'post_type'   => static::POST_TYPE,
			'exclude'     => $saved_menus,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields'      => 'ids',
		] );

		foreach ( $menus as $menu_id ) {
			wp_delete_post( $menu_id, true );
		}
	}
}
