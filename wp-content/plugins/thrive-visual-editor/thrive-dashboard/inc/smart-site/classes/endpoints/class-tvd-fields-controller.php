<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TVD_Fields_Controller extends TVD_REST_Controller {

	/**
	 * @var string Base name
	 */
	public $base = 'fields';

	/**
	 * Register Routes
	 */
	public function register_routes() {
		register_rest_route( static::$namespace . static::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_field' ),
				'permission_callback' => array( $this, 'fields_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_field' ),
				'permission_callback' => array( $this, 'fields_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_field' ),
				'permission_callback' => array( $this, 'fields_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/save_fields/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_fields' ),
				'permission_callback' => array( $this, 'fields_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Add multiple fields at once
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_fields( $request ) {
		$models   = $request->get_param( 'models' );
		$response = array();

		foreach ( $models as $model ) {

			$data = TVD_Smart_DB::save_field( $model, empty( $model['id'] )? 'insert': 'update' );

			if ( $data ) {
				$response[] = $data;
			} else {
				return new WP_Error( 'error', __( 'Something went wrong while saving the fields, please refresh and try again. If the problem persists please contact our support team.', 'thrive-dash' ) );
			}
		}

		return new WP_REST_Response( $response, 200 );

	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function add_field( $request ) {
		$model = $request->get_params();

		$model = TVD_Smart_DB::save_field( $model, 'insert' );
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
	public function delete_field( $request ) {

		$id = $request->get_param( 'id' );

		$result = TVD_Smart_DB::delete_field( $id );

		if ( $result ) {
			return new WP_REST_Response( true, 200 );
		}

		return new WP_Error( 'no-results', __( 'No field was deleted!', 'thrive-dash' ) );
	}

	/**
	 * Edit a group
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_field( $request ) {
		$model = $request->get_params();

		$model = TVD_Smart_DB::save_field( $model, 'update' );

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
	public function fields_permissions_check( $request ) {
		return current_user_can( TVE_DASH_CAPABILITY );
	}
}
