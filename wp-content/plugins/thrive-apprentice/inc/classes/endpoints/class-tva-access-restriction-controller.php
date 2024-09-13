<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/23/2018
 * Time: 17:46
 */

class TVA_Access_Restriction_Controller extends TVA_REST_Controller {
	/**
	 * @var string
	 */
	public $base = 'access-restriction';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		/**
		 * Create a new page to be used in redirect options
		 */
		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/redirect-page', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_redirect_page' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'op'    => [
						'type'     => 'string',
						'required' => true,
						'enum'     => [ 'search', 'create', 'delete' ],
					],
					'title' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

		/**
		 * Save settings for a scope
		 */
		register_rest_route( static::$namespace . static::$version, '/' . $this->base, [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'save_settings' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					// context where this is being applied
					'scope' => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'not_logged', 'not_purchased', 'locked', 'custom', 'action_button_display' ],
					],
				],
			],
		] );

		/**
		 * Delete custom settings for a product
		 */
		register_rest_route( static::$namespace . static::$version, '/' . $this->base, [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_settings' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					// context where this is being applied
					'scope' => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'custom' ],
					],
					'id'    => [
						'required' => true,
						'type'     => 'int',
					],
				],
			],
		] );

		/**
		 * Ensure a post exists for a scope, with the `content` option selected
		 */
		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/ensure-data', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'ensure_data' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					// context where this is being applied
					'scope'  => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'not_logged', 'not_purchased', 'locked', 'custom' ],
					],
					// option selected by the user
					'option' => [
						'required' => true,
						'type'     => 'string',
						'enum'     => array_keys( TVA_Access_Restriction::get_possible_options() ),
					],
					// optional, a course ID
					'course' => [
						'type' => 'int',
					],
				],
			],
		] );

		/**
		 * Deletes a custom post that was created for the preview settings for a product
		 */
		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/delete_custom_post', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_custom_post' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'course_id' => [
						'required' => true,
						'type'     => 'int',
					],
					// context where this is being applied
					'scope'     => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'custom' ],
					],
				],
			],
		] );

	}

	/**
	 * Delete a custom setting
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function delete_settings( $request ) {
		$course_id = (int) $request->get_param( 'course' );
		$scope     = $request->get_param( 'scope' );
		$settings  = tva_access_restriction_settings( $course_id );
		$custom_id = (int) $request->get_param( 'id' );

		if ( is_null( $settings->get( $scope ) ) ) {
			return new WP_Error( 422, 'Invalid scope: ' . $scope );
		}

		$custom_settings = $settings->get( $scope );

		foreach ( $custom_settings as $index => $setting ) {
			if ( $setting['custom_id'] === $custom_id ) {
				if ( $setting['option'] === 'content' ) {
					wp_delete_post( $setting['content']['post_id'] );
				}
				array_splice( $custom_settings, $index, 1 );
				break;
			}
		}

		$settings->set( $scope, $custom_settings )
				 ->save();

		return new WP_REST_Response( $custom_id );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_redirect_page( $request ) {
		$post_id = $request->get_param( 'value' );
		switch ( $request['op'] ) {
			case 'create':
				$post_id = wp_insert_post( [
					'post_title'  => $request['title'],
					'post_type'   => 'page',
					'post_status' => 'publish',
				] );

				if ( is_wp_error( $post_id ) ) {
					return $post_id;
				}

				/* mark as temporary editable with TAr */
				TVA_Access_Restriction::mark_temporary_redirect_page( $post_id );
				break;

			case 'search':
				/* mark the page as editable (temporary) */
				TVA_Access_Restriction::mark_temporary_redirect_page( $post_id );
				break;
			case 'delete':
				/* remove the previous temporary restriction */
				TVA_Access_Restriction::remove_temporary_redirect_page( $request->get_param( 'previous_id' ) );
				break;
			default:
				break;
		}

		$page = new TVA_Page_Setting( 'custom_redirect', 'general', $post_id );

		return rest_ensure_response( $page->to_array() );
	}


	/**
	 * Store the settings to the database
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function save_settings( $request ) {
		$course_id = (int) $request->get_param( 'course' );
		$scope     = $request->get_param( 'scope' );
		$settings  = tva_access_restriction_settings( $course_id );

		if ( is_null( $settings->get( $scope ) ) ) {
			return new WP_Error( 422, 'Invalid scope: ' . $scope );
		}

		$option = $request->get_param( 'option' );
		if ( empty( $option ) ) {
			return new WP_Error( 422, 'Invalid option' );
		}

		if ( $scope === 'custom' && $settings instanceof TVA_Product_Access_Restriction ) {
			$custom_id = $request->get_param( 'custom_id' );
			$post_id   = $request->get_param( 'content' )['post_id'];

			$updated_settings = [
				'option'     => $option,
				"{$option}"  => $request->get_param( $option ),
				'conditions' => $request->get_param( 'conditions' ),
				'title'      => $request->get_param( 'title' ),
				'order'      => $request->get_param( 'order' ),
			];

			$updated_settings_index = $settings->save_custom_settings( $custom_id, $updated_settings, $scope, $post_id );
		} else {
			$settings->set( "{$scope}.option", $option )
					 ->set( "{$scope}.{$option}", $request->get_param( $option ) )
					 ->ensure_data_exists( null );
		}

		$settings->save()
				 ->admin_localize(); // to make sure all the extra data is returned correctly and completely

		$response = isset( $updated_settings_index ) ? $settings->get( $scope )[ $updated_settings_index ] : $settings->get( $scope );

		return rest_ensure_response( $response );
	}

	/**
	 * Ensure all needed data exists.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function ensure_data( $request ) {
		$scope     = $request->get_param( 'scope' );
		$course    = $request->get_param( 'course' );
		$option    = $request->get_param( 'option' );
		$custom_id = $request->get_param( 'custom_id' );
		$settings  = tva_access_restriction_settings( $course );

		if ( is_null( $settings->get( $scope ) ) ) {
			return new WP_Error( 422, 'Invalid scope: ' . $scope );
		}

		if ( ! empty( $request->get_param( 'content' ) ) && ! empty( $request->get_param( 'content' )['post_id'] ) ) {
			return rest_ensure_response( $request->get_params() );
		}

		if ( $custom_id === '' ) {
			//it is a standard setting
			$settings->set( "{$scope}.option", $option );
		}

		$settings->ensure_data_exists( $custom_id ); // no SAVE needed here. make sure this is not persisted
		$settings->admin_localize();

		if ( $scope !== 'custom' ) {
			return rest_ensure_response( $settings->get( $scope ) );

		}

		return rest_ensure_response( $settings->get( $scope )[ - 1 ] );
	}

	/**
	 * Ensure all needed data exists.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function delete_custom_post( $request ) {
		$scope     = $request->get_param( 'scope' );
		$course_id = $request->get_param( 'course_id' );

		$settings = tva_access_restriction_settings( $course_id );
		$settings->delete_tmp_settings( $scope );
		$custom_settings = $settings->get( $scope );
		unset( $custom_settings[ - 1 ] );

		$settings->set( $scope, $custom_settings )
				 ->save()
				 ->admin_localize();

		return rest_ensure_response( true );
	}
}
