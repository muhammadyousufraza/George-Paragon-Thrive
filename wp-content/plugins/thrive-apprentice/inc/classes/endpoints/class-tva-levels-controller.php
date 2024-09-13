<?php

class TVA_Levels_Controller extends TVA_REST_Controller {

	public $base = 'levels';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'levels_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'levels_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'levels_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	public function create_item( $request ) {
		$new_level = new TVA_Level( [ 'name' => $request->get_param( 'name' ) ] );

		$result = $new_level->save();

		if ( $result ) {
			return new WP_REST_Response( $new_level, 200 );
		}

		return new WP_Error( 'no-results', __( 'No level was updated!', 'thrive-apprentice' ) );
	}

	public function levels_permissions_check( $request ) {
		return TVA_Product::has_access();
	}

	public function delete_item( $request ) {
		$level  = new TVA_Level( intval( $request->get_param( 'ID' ) ) );
		$result = $level->delete();

		if ( $result ) {
			return new WP_REST_Response( $result, 200 );
		}

		return new WP_Error( 'no-results', __( 'No level was deleted!', 'thrive-apprentice' ) );
	}

	/**
	 * Update a level name
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$id   = $request->get_param( 'id' );
		$name = $request->get_param( 'name' );

		$level = new TVA_Level( $id );
		$level->set_name( $name );
		$result = $level->save();

		if ( $result ) {
			return new WP_REST_Response( $level, 200 );
		}

		return new WP_Error( 'no-results', __( 'No level was modified!', 'thrive-apprentice' ) );
	}
}
