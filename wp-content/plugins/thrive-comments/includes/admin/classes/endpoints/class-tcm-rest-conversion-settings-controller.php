<?php

/**
 * Class TCM_REST_Settings_Controller
 */
class TCM_REST_Conversion_Settings_Controller extends TCM_REST_Settings_Controller {
	/**
	 * Base rest url
	 *
	 * @var string $base
	 */
	public $base = 'settings';

	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/' . 'redirect_autocomplete', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'redirect_autocomplete' ),
				'permission_callback' => array( $this, 'autocomplete_settings_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/' . 'get_post_name_by_id', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'get_post_name_by_id' ),
				'permission_callback' => array( $this, 'autocomplete_settings_permissions_check' ),
			),
		) );
	}

	/**
	 * Permission checks for getting posts for autocomplete
	 *
	 * @return bool
	 */
	public function autocomplete_settings_permissions_check() {
		return current_user_can( 'moderate_comments' );
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function redirect_autocomplete( $request ) {
		global $wpdb;
		if ( ! $this->param( 'q' ) ) {
			wp_die( 0 );
		}

		$q_param = $this->param( 'q' );
		$s       = wp_unslash( $q_param );

		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_title LIKE '%s' AND post_type in ('post', 'page') LIMIT 10", '%' . $wpdb->esc_like( $s ) . '%' ) );

		$json = array();
		foreach ( $posts as $post ) {
			if ( $post->ID ) {
				$json [] = array(
					'id'    => $post->ID,
					'label' => $post->post_title,
					'value' => $post->post_title,
				);
			}
		}

		/**
		 * Filters the json response for the posts autocomplete
		 *
		 * @param array           $json    - the default json with all the posts from the website
		 * @param WP_REST_Request $request The request from autocomplete
		 *
		 * @since 1.0.1
		 *
		 */
		$json = apply_filters( 'tcm_posts_autocomplete', $json, $request );

		wp_send_json( $json );
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function get_post_name_by_id( $request ) {

		$post_id = (int) $request->get_param( 'postId' );
		$title   = '';

		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post->post_title ) {
				$title = $post->post_title;
			}
		}

		wp_send_json( $title );
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}
}
