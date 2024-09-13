<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Trait Thrive_Term_Meta
 *
 * Interacts with term meta
 *
 * @property int|string $ID assumes the instance always has an ID
 */
trait Thrive_Term_Meta {

	/**
	 * Register generic routes for get/update for term meta
	 *
	 * @param string $namespace
	 * @param string $route
	 * @param null   $permission_callback
	 */
	public static function register_term_routes( $namespace = '', $route = '', $permission_callback = null ) {
		register_rest_route( $namespace, $route . '/meta', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'rest_get_term_meta' ],
				'permission_callback' => $permission_callback,
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'rest_update_term_meta' ],
				'permission_callback' => $permission_callback,
			],
		] );
	}

	/**
	 * Read data from term meta
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function rest_get_term_meta( $request ) {

		$id  = $request->get_param( 'id' );
		$key = $request->get_param( 'key' );

		$value = get_term_meta( $id, $key, true );

		return new WP_REST_Response( $value, 200 );
	}

	/**
	 * Update data on term meta
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function rest_update_term_meta( $request ) {

		$id    = $request->get_param( 'id' );
		$key   = $request->get_param( 'key' );
		$value = $request->get_param( 'value' );

		$result = update_term_meta( $id, $key, $value );

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Get the meta value for the given meta field..
	 *
	 * @param string $meta_field
	 * @param mixed  $default_value default value to return if meta_value not found
	 *
	 * @return mixed
	 */
	public function get_meta( $meta_field = '', $default_value = null ) {
		$result = get_term_meta( $this->ID, $meta_field, true );

		if ( $meta_field && ( $result === null || $result === false ) ) {
			$result = $default_value;
		}

		return $result;
	}

	/**
	 * Set the meta value for the given meta field.
	 *
	 * @param $meta_field
	 * @param $meta_value
	 *
	 * @return $this fluent interface
	 */
	public function set_meta( $meta_field, $meta_value ) {
		update_term_meta( $this->ID, $meta_field, $meta_value );

		return $this;
	}

	/**
	 * Deletes a meta field
	 *
	 * @param string $meta_field
	 *
	 * @return $this fluent interface
	 */
	public function delete_meta( $meta_field ) {
		delete_term_meta( $this->ID, $meta_field );

		return $this;
	}
}
