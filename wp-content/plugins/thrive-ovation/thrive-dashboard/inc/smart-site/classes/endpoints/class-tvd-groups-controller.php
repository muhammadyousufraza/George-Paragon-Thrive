<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TVD_Groups_Controller extends TVD_REST_Controller {

	/**
	 * @var string Base name
	 */
	public $base = 'groups';

	/**
	 * Register Routes
	 */
	public function register_routes() {
		register_rest_route( static::$namespace . static::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_group' ),
				'permission_callback' => array( $this, 'groups_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_group' ),
				'permission_callback' => array( $this, 'groups_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_group' ),
				'permission_callback' => array( $this, 'groups_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Add a group
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function add_group( $request ) {
		$model               = $request->get_params();

		$model = TVD_Smart_DB::insert_group( $model );

		if ( $model ) {
			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( 'The group was not added, please try again !', 'thrive-dash' ) );
	}

	/**
	 * Delete a group and all it's fields
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_group( $request ) {

		$id = $request->get_param( 'id' );

		$result = TVD_Smart_DB::delete_group( $id );

		if ( $result ) {
			return new WP_REST_Response( true, 200 );
		}

		return new WP_Error( 'no-results', __( 'No group was deleted!', 'thrive-dash' ) );
	}

	/**
	 * Edit a group
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_group( $request ) {
		$model               = $request->get_params();

		$model = TVD_Smart_DB::update_group( $model );

		if ( $model ) {
			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( 'No group was updated!', 'thrive-dash' ) );
	}

	/**
	 * Permissions check
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function groups_permissions_check( $request ) {
		return current_user_can( TVE_DASH_CAPABILITY );
	}
}
