<?php

class TVA_Bundle_Controller extends WP_REST_Controller {

	protected $rest_base = 'bundles';

	public function register_routes() {

		register_rest_route( 'tva/v1', $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );

		register_rest_route( 'tva/v1', $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );

		register_rest_route( 'tva/v1', $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );

		register_rest_route( 'tva/v1', $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Bundle[]|WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		return TVA_Course_Bundles_Manager::get_bundles();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Bundle|WP_Error
	 */
	public function create_item( $request ) {

		$data = array(
			'name'     => sanitize_text_field( $request->get_param( 'name' ) ),
			'products' => $request->get_param( 'products' ),
		);

		return TVA_Course_Bundles_Manager::create_bundle( $data );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Bundle|WP_Error
	 */
	public function update_item( $request ) {

		$data = array(
			'id'       => (int) sanitize_text_field( $request->get_param( 'id' ) ),
			'name'     => sanitize_text_field( $request->get_param( 'name' ) ),
			'products' => ( array ) $request->get_param( 'products' ),
		);

		return TVA_Course_Bundles_Manager::update_bundle( $data );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_item( $request ) {

		return TVA_Bundle::delete( (int) $request->get_param( 'id' ) );
	}
}
