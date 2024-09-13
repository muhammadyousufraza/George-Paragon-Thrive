<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\RestApi;

use TCB\ConditionalDisplay\Condition;
use TCB\ConditionalDisplay\Entity;
use TCB\ConditionalDisplay\Field;
use TCB\ConditionalDisplay\PostTypes\Conditional_Display_Group;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class General_Data
 *
 * @package TCB\ConditionalDisplay\RestApi
 */
class General_Data {
	const  REST_NAMESPACE = 'tcb/v1';
	const  REST_ROUTE = 'conditional-display/';

	public static function register_routes() {
		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'groups', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_groups' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'groups' => [
						'type'     => 'array',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'fields', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_fields' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'entity' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'conditions', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_conditions' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'field' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'field-options', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_field_options' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'field' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );
	}

	/**
	 * @param $request \WP_REST_Request
	 *
	 * @return array
	 */
	public static function get_groups( $request ) {

		$group_keys = $request->get_param( 'groups' );
		$query_vars = $request->get_param( 'query_vars' );

		if ( ! empty( $query_vars ) ) {
			tve_set_query_vars_data( $query_vars );
		}

		if ( is_array( $group_keys ) ) {
			foreach ( $group_keys as $display_group_key ) {
				$display_group = Conditional_Display_Group::get_instance( $display_group_key );

				if ( $display_group !== null ) {
					$display_group->localize( false, true );
				}
			}

			$groups = array_values( $GLOBALS['conditional_display_preview'] );
		} else {
			$groups = [];
		}

		return $groups;
	}

	/**
	 * @param $request \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_fields( $request ) {
		$entity_key = $request->get_param( 'entity' );

		$field_data = [];

		$entity = Entity::get();

		$fields = $entity[ $entity_key ]::get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field_key ) {
				$field_class = Field::get()[ $field_key ];

				$field_data[ $field_key ] = $field_class::get_data_to_localize();
			}
		}

		return new \WP_REST_Response( $field_data );
	}

	/**
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_conditions( $request ) {
		$field_key = $request->get_param( 'field' );

		$condition_data = [];
		$field      = Field::get();

		if ( ! empty( $field[ $field_key ] ) ) {
			$conditions = $field[ $field_key ]::get_conditions();

			if ( ! empty( $conditions ) ) {
				foreach ( $conditions as $condition_key ) {
					$condition_class = Condition::get()[ $condition_key ];

					$condition_data[ $condition_key ] = $condition_class::get_data_to_localize();
				}
			}
		}

		return new \WP_REST_Response( $condition_data );
	}

	/**
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_field_options( $request ) {
		$options   = [];
		$field_key = $request->get_param( 'field' );

		if ( ! empty( Field::get()[ $field_key ] ) ) {
			$selected_values = $request->get_param( 'values' );
			$search          = $request->get_param( 'search' );

			$field   = Field::get();
			$options = $field[ $field_key ]::get_options( $selected_values, $search );
		}

		return new \WP_REST_Response( $options );
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
