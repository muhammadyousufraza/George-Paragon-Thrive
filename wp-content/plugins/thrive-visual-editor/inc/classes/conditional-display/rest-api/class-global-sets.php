<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\RestApi;

use TCB\ConditionalDisplay\PostTypes\Global_Conditional_Set;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Sets
 *
 * @package TCB\ConditionalDisplay\RestApi
 */
class Global_Sets {
	const  REST_NAMESPACE = 'tcb/v1';
	const  REST_ROUTE = 'conditional-display/global-set';

	public static function register_routes() {
		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE, [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'update_global_set' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'label' => [
						'type'     => 'string',
						'required' => true,
					],
					'rules' => [
						'type'     => 'array',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . '/(?P<id>[\d]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_global_set_data' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update_global_set' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'label' => [
						'type'     => 'string',
						'required' => true,
					],
					'rules' => [
						'type'     => 'array',
						'required' => true,
					],
				],
			],
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'remove_global_set' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . '/get-all', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_global_sets' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'search' => [
						'type'     => 'string',
						'required' => false,
					],
				],
			],
		] );
	}

	/**
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_global_set_data( $request ) {
		$data = [];

		$post_id = $request->get_param( 'id' );

		$global_set = Global_Conditional_Set::get_instance( $post_id );

		if ( ! empty( $global_set->get_post() ) ) {
			$data = [
				'id'    => $post_id,
				'rules' => $global_set->get_rules(),
				'label' => $global_set->get_label(),
			];
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_global_sets( $request ) {
		$searched_keyword = $request->get_param( 'search' );

		$global_sets = Global_Conditional_Set::get_sets_by_name( $searched_keyword );

		return new \WP_REST_Response( $global_sets );
	}

	/**
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function update_global_set( $request ) {
		$post_id = $request->get_param( 'id' );
		$label   = $request->get_param( 'label' );

		$sets_with_this_label = Global_Conditional_Set::get_sets_by_name( $label, true );

		if (
			! empty( $sets_with_this_label ) &&
			(
				empty( $post_id ) || /* case 1: no post ID means we're adding a new global set and the label already exists */
				count( $sets_with_this_label ) > 1 /* case 2: if there is a post ID: the only global set found for this label should be the current one */
			)
		) {
			return new \WP_Error( 'tcb_error', __( 'A global set with this name already exists!', 'thrive-cb' ), [ 'status' => 409 ] );
		}

		$rules = $request->get_param( 'rules' );

		if ( empty( $post_id ) ) {
			$global_set = Global_Conditional_Set::get_instance();
			$post_id    = $global_set->create( $rules, $label );
		} else {
			$global_set = Global_Conditional_Set::get_instance( $post_id );
			$global_set->update( $rules, $label );
		}

		return new \WP_REST_Response( $post_id );
	}

	/**
	 *
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function remove_global_set( $request ) {
		$post_id = $request->get_param( 'id' );

		$global_set = Global_Conditional_Set::get_instance( $post_id );
		$global_set->remove();

		return new \WP_REST_Response( $post_id );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @return \WP_Error|bool
	 */
	public static function route_permission() {
		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : null;

		return \TCB_Product::has_external_access( $post_id );
	}
}
