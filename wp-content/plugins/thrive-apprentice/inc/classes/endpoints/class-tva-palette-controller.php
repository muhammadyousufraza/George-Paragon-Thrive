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
 * Class TVA_Palette_Controller
 *
 * @project  : thrive-apprentice
 */
class TVA_Palette_Controller extends TVA_REST_Controller {
	public $base = 'palette';

	public function register_routes() {
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<skin_id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update' ],
				'permission_callback' => [ $this, 'route_permission' ],
				'args'                => [
					'skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'hsl'     => [
						'type'     => 'object',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<skin_id>[\d]+)/update_auxiliary_variable', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_auxiliary' ],
				'permission_callback' => [ $this, 'route_permission' ],
				'args'                => [
					'skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'id'      => [
						'type'     => 'integer',
						'required' => true,
					],
					'color'   => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );
	}

	/**
	 * Update skin palette configuration and master variables settings
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$hsl  = $request->get_param( 'hsl' );
		$skin = \TVA\TTB\Main::skin( $request->get_param( 'skin_id' ) );

		\TVA\TTB\tva_palettes()->update_master_hsl( $hsl );
		$config = $skin->get_palettes();

		$active_id = (int) $config['active_id'];

		$config['palettes'][ $active_id ]['modified_hsl']['h'] = (int) $hsl['h'];
		$config['palettes'][ $active_id ]['modified_hsl']['s'] = (float) $hsl['s'];
		$config['palettes'][ $active_id ]['modified_hsl']['l'] = (float) $hsl['l'];

		$skin->update_palettes( $config, 2 );

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}

	/**
	 * Update auxiliary colors callback
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function update_auxiliary( $request ) {
		$id    = (int) $request->get_param( 'id' );
		$color = (string) $request->get_param( 'color' );

		\TVA\TTB\tva_palettes()->update_auxiliary_variable( $id, $color );

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}

	/**
	 * This should only be available for admins
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function route_permission( $request ) {
		return current_user_can( 'manage_options' );
	}
}
