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
 * Completed Post controller
 */
class TVA_Completed_Post_Controller extends TVA_REST_Controller {
	public $base = 'completed_post';

	/**
	 * Registers REST routes
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route( static::$namespace . static::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => [
					'course_id' => [
						'required' => true,
						'type'     => 'int',
					],
				],
			),
		) );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/custom-page', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'custom_page' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => [
					'course_id' => [
						'required' => true,
						'type'     => 'int',
					],
					'state'     => [
						'required' => true,
						'type'     => 'string',
						'enum'     => array( 'search', 'create', 'delete', 'normal' ),
					],
				],
			),
		) );
		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<id>[\d]+)/change_type', [
			[

				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_type' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'course_id' => [
						'required' => true,
						'type'     => 'int',
					],
					'type'      => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'completed', 'custom' ],
					],
				],

			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<id>[\d]+)', [

			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'course_id'      => [
						'required' => true,
						'type'     => 'int',
					],
					'title'          => [
						'required' => true,
						'type'     => 'string',
					],
					'post_name'      => [
						'required' => true,
						'type'     => 'string',
					],
					'allow_comments' => [
						'required' => true,
						'type'     => 'int',
					],
				],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'args' => [
						'id' => [
							'required' => true,
							'type'     => 'int',
						],
					],
				],
			],
		] );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function create_item( $request ) {
		$course = new TVA_Course_V2( (int) $request->get_param( 'course_id' ) );

		return new WP_REST_Response( $course->get_completed_post( true ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function custom_page( $request ) {
		$course         = new TVA_Course_V2( (int) $request->get_param( 'course_id' ) );
		$completed_post = $course->get_completed_post();

		switch ( $request->get_param( 'state' ) ) {
			case 'search':
				$completed_post->update_extra_id( (int) $request->get_param( 'value' ) );
				break;
			case 'create':
				$post_id = wp_insert_post( array(
					'post_title'  => sanitize_text_field( $request->get_param( 'title' ) ),
					'post_type'   => 'page',
					'post_status' => 'publish',
				) );

				if ( ! is_wp_error( $post_id ) ) {
					$completed_post->update_extra_id( $post_id );
				}

				break;
			case 'delete':
				$completed_post->update_extra_id( 0 );
				break;
			case 'normal':
				$url = (string) $request->get_param( 'custom_link' );

				if ( ! empty( $url ) && wp_http_validate_url( $url ) ) {
					$completed_post->update_extra_id( $url );
				}
				break;
			default:
				break;
		}

		return new WP_REST_Response( $completed_post );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$type   = (string) $request->get_param( 'type' );
		$course = new TVA_Course_V2( (int) $request->get_param( 'course_id' ) );

		$completed_post = $course->get_completed_post();

		if ( $type === TVA_Course_Completed::COMPLETED_TYPE ) {

			$title          = sanitize_text_field( $request->get_param( 'title' ) );
			$post_name      = sanitize_text_field( $request->get_param( 'post_name' ) );
			$allow_comments = (int) $request->get_param( 'allow_comments' );

			wp_update_post( [
				'ID'             => $completed_post->ID,
				'post_title'     => $title,
				'post_name'      => $post_name,
				'comment_status' => $allow_comments === 1 ? 'open' : 'closed',
			] );
			$completed_post->title     = $title;
			$completed_post->comments  = $allow_comments;
			$completed_post->post_name = $post_name;
		}

		return new WP_REST_Response( $completed_post );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update_type( $request ) {
		$course = new TVA_Course_V2( (int) $request->get_param( 'course_id' ) );

		$completed_post = $course->get_completed_post();

		update_post_meta( $completed_post->ID, 'tva_completed_type', (string) $request->get_param( 'type' ) );

		return new WP_REST_Response( $completed_post );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$completed_post_id = $request->get_param( 'id' );
		wp_delete_post( $completed_post_id, true );

		return new WP_REST_Response( $completed_post_id );
	}
}
