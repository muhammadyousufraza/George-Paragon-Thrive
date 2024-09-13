<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Logo_REST
 */
class TCB_Logo_REST {

	public static $namespace = 'tcb/v1';
	public static $route = '/logo';

	public static $rename_route = '/rename_logo';

	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Registers the route for adding/deleting/editing/renaming new logos.
	 */
	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ 'TCB_Logo_REST', 'add' ],
				'permission_callback' => [ 'TCB_Logo_REST', 'route_permission' ],
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ 'TCB_Logo_REST', 'update' ],
				'permission_callback' => [ 'TCB_Logo_REST', 'route_permission' ],
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ 'TCB_Logo_REST', 'delete' ],
				'permission_callback' => [ 'TCB_Logo_REST', 'route_permission' ],
			),
		) );

		register_rest_route( static::$namespace, static::$route . static::$rename_route . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ 'TCB_Logo_REST', 'rename_logo' ],
				'permission_callback' => [ 'TCB_Logo_REST', 'route_permission' ],
			),
		) );
	}

	/**
	 * Add a new logo.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function add( $request ) {
		$attachment_id = $request->get_param( 'attachment_id' );
		$name          = $request->get_param( 'name' );
		$scope         = $request->get_param( 'scope' );

		/* added logos are active by default */
		$active = 1;

		/* default is 0, only the two initial logos have this value set to 1 */
		$default = 0;

		$logos = TCB_Logo::get_logos();

		/* search for an already existing logo with the same $attachment_id */
		foreach ( $logos as $index => $logo ) {
			if ( $attachment_id && (int) $logo['attachment_id'] === (int) $attachment_id ) {
				$new_logo = $logo;
				break;
			}
		}

		if ( ! isset( $new_logo ) ) {
			$logo_id = count( $logos );

			$new_logo = [
				'id'            => $logo_id,
				'attachment_id' => $attachment_id,
				'name'          => $name,
				'default'       => $default,
				'active'        => $active,
				'scope'         => $scope,
			];

			/* add the new logo to the logo array */
			$logos[] = $new_logo;

			/* update inside the DB */
			update_option( TCB_Logo::OPTION_NAME, $logos );
		}

		/* return the new logo ID */

		return new WP_REST_Response( $new_logo, 200 );
	}

	/**
	 * Update an existing logo.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function update( $request ) {
		$id = (int) $request->get_param( 'id' );

		/* also cover the case where ID is 0 */
		if ( isset( $id ) ) {
			$index = - 1;

			$logos = TCB_Logo::get_logos();

			/* look for the logo data in the array */
			foreach ( $logos as $key => $logo_data ) {
				if ( $id === $logo_data['id'] ) {
					$index = $key;
					break;
				}
			}

			/* if we found the ID in the array, update it with the new params */
			if ( $index !== - 1 ) {
				$attachment_id = $request->get_param( 'attachment_id' );

				/* update data in the array */
				$logos[ $index ]['attachment_id'] = $attachment_id;

				/* update data inside the DB */
				update_option( TCB_Logo::OPTION_NAME, $logos );

				return new WP_REST_Response( 'success', 200 );
			}
		}

		return new WP_REST_Response( 'Error on updating the logo - the ID was not sent, or the logo was not found in the array.', 500 );
	}

	/**
	 * Delete an existing logo.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function delete( $request ) {
		$id = (int) $request->get_param( 'id' );

		if ( ! empty( $id ) ) {
			$index = - 1;
			$logos = TCB_Logo::get_logos();

			/* look for the logo data in the array */
			foreach ( $logos as $key => $logo_data ) {
				if ( $id === $logo_data['id'] ) {
					$index = $key;
					break;
				}
			}

			/* if we found the ID in the array, update it with the new params */
			if ( $index !== - 1 ) {

				/* make the logo inactive */
				$logos[ $index ]['active'] = 0;

				/* update inside the DB */
				update_option( TCB_Logo::OPTION_NAME, $logos );

				return new WP_REST_Response( 'success', 200 );
			}
		}

		return new WP_REST_Response( 'Error on deleting the logo - ID was not sent, or the logo was not found in the array.', 500 );
	}

	/**
	 * Rename a logo.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function rename_logo( $request ) {
		$id = (int) $request->get_param( 'id' );

		if ( isset( $id ) ) {
			$index = - 1;

			$logos = TCB_Logo::get_logos();

			/* look for the logo data in the array */
			foreach ( $logos as $key => $logo_data ) {
				if ( $id === $logo_data['id'] ) {
					$index = $key;
					break;
				}
			}

			/* if we found the ID in the array, update it with the new name */
			if ( $index !== - 1 ) {
				$name = $request->get_param( 'name' );

				/* update data in the array */
				$logos[ $index ]['name'] = $name;

				/* update data inside the DB */
				update_option( TCB_Logo::OPTION_NAME, $logos );

				return new WP_REST_Response( 'success', 200 );
			}
		}

		return new WP_REST_Response( 'Error on renaming the logo - the ID was not sent, or the logo was not found in the array.', 500 );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function route_permission( $request ) {
		return TCB_Product::has_external_access();
	}
}

new TCB_Logo_REST();
