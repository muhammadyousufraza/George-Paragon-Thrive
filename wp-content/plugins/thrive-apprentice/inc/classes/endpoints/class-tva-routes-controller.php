<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVA_Routes_Controller extends TVA_REST_Controller {
	public $base = 'routes';

	public function register_routes() {
		register_rest_route( static::$namespace . static::$version,
			$this->base,
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_route' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => [
						'identifier' => [
							'type'        => 'string',
							'description' => 'Route identifier',
							'required'    => true,
						],
						'route'      => [
							'type'        => 'string',
							'description' => 'New route',
							'required'    => true,
						],
					],
				],
			]
		);
	}

	public function update_route( WP_REST_Request $request ) {
		$identifier = $request->get_param( 'identifier' );
		$route      = $request->get_param( 'route' );

		$response = TVA_Routes::update_route( $identifier, $route );

		return new WP_REST_Response( $response, $response instanceof WP_Error ? 401 : 200 );
	}
}
