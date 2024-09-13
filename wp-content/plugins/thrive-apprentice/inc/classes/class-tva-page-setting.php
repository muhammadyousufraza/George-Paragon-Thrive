<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Page_Setting
 *
 * @project  : thrive-apprentice
 */
class TVA_Page_Setting extends TVA_Setting {

	/**
	 * @return string
	 */
	public function get_title() {
		$value = $this->get_value();

		return ! empty( $value ) ? get_the_title( $value ) : '';
	}

	/**
	 * @return false|string|WP_Error
	 */
	public function get_link() {
		$id = $this->get_value();

		return empty( $id ) ? '' : get_permalink( $id );
	}


	public function get_editor_link() {
		return tcb_get_editor_url( $this->get_value() );
	}

	public function get_wp_editor_link() {
		$id = $this->get_value();

		return empty( $id ) ? '' : get_edit_post_link( $id, '' );
	}

	/**
	 * @return array
	 */
	public function to_array() {

		return array_merge(
			parent::to_array(),
			array(
				'title'        => $this->get_title(),
				'preview_url'  => $this->get_link(),
				'edit_url'     => $this->get_editor_link(),
				'edit_with_wp' => $this->get_wp_editor_link(),
			)
		);
	}

	/**
	 * @param array $data
	 *
	 * @return false|int
	 */
	public function add( $data = array() ) {

		$post_id = wp_insert_post( array(
			'post_title'  => isset( $data['title'] ) ? $data['title'] : '',
			'post_type'   => 'page',
			'post_status' => 'publish',
		) );

		return ! is_wp_error( $post_id ) ? $post_id : false;
	}
}
