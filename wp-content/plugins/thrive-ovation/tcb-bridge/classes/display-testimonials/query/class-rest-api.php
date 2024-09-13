<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Rest_Api {
	const  REST_NAMESPACE = 'tcb/v1';
	const  REST_ROUTE     = 'display-testimonials/';

	public static function register_routes() {
		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'testimonials', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_testimonials' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'values'         => [
						'type'     => 'array',
						'required' => false,
					],
					'page'           => [
						'type'     => 'int',
						'required' => true,
					],
					'filters'        => [
						'type'     => 'object',
						'required' => false,
					],
					'items_per_page' => [
						'type'     => 'int',
						'required' => true,
					],
				],
			],
		] );
		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'testimonials-count', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_testimonials_count' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'filters' => [
						'type'     => 'object',
						'required' => false,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'sets', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'add_or_update_set' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'type'         => [
						'type'     => 'string',
						'required' => true,
					],
					'label'        => [
						'type'     => 'string',
						'required' => true,
					],
					'ordering'     => [
						'type'     => 'object',
						'required' => false,
					],
					'count'        => [
						'type'     => 'int',
						'required' => false,
					],
					'testimonials' => [
						'type'     => 'array',
						'required' => false,
					],
					'tags'         => [
						'type'     => 'array',
						'required' => false,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'sets/(?P<id>[\d]+)', [
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'add_or_update_set' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'type'         => [
						'type'     => 'string',
						'required' => true,
					],
					'label'        => [
						'type'     => 'string',
						'required' => true,
					],
					'ordering'     => [
						'type'     => 'object',
						'required' => false,
					],
					'count'        => [
						'type'     => 'int',
						'required' => false,
					],
					'testimonials' => [
						'type'     => 'array',
						'required' => false,
					],
					'tags'         => [
						'type'     => 'array',
						'required' => false,
					],
				],
			],
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'delete_set' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'tags', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_tags' ],
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
	 * @param $request \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_testimonials( $request ) {
		$page           = (int) $request->get_param( 'page' );
		$filters        = $request->get_param( 'filters' );
		$values         = $request->get_param( 'values' );
		$items_per_page = $request->get_param( 'items_per_page' );

		if ( empty( $values ) ) {
			$testimonials = Filters::get_filtered_testimonials( $filters );
		} else {
			$testimonials = Main::get_testimonials( $values );
		}

		$total = count( $testimonials );

		if ( ! empty( $page ) ) {
			/* paginate the requests */
			$testimonials = array_slice( $testimonials, ( $page - 1 ) * $items_per_page, $items_per_page );
		}

		return new \WP_REST_Response( [
			'items' => $testimonials,
			'total' => $total
		] );
	}

	public static function get_testimonials_count( $request ) {
		return new \WP_REST_Response( count( Filters::get_filtered_testimonials( $request->get_param( 'filters' ) ) ) );
	}

	public static function add_or_update_set( $request ) {
		$type = $request->get_param( 'type' );
		$data = [
			'label'    => $request->get_param( 'label' ),
			'ordering' => $request->get_param( 'ordering' ),
		];

		$items_key          = ( $type === 'static' ) ? 'testimonials' : 'tags';
		$data[ $items_key ] = $request->get_param( $items_key );

		$id = $request->get_param( 'id' );

		$response = [];

		if ( isset( $id ) ) {
			Set::get_instance_with_id( $id )->update( $data );
		} else {
			$response = Set::add( $data );
		}

		/* return the updated count */
		$response['count'] = Set::get_testimonial_count( $data );

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 *
	 * @param \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function delete_set( $request ) {
		$id = (int) $request->get_param( 'id' );

		$set = Set::get_instance_with_id( $id );
		$set->delete();

		return new \WP_REST_Response( $id, 200 );
	}

	/**
	 * @param $request \WP_REST_Request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_tags( $request ) {
		$search = empty( $request->get_param( 'search' ) ) ? '' : $request->get_param( 'search' );
		$terms  = get_terms( [
			'taxonomy'   => TVO_TESTIMONIAL_TAG_TAXONOMY,
			'hide_empty' => false,
			'search'     => $search
		] );

		$tag_testimonial_count_map = Main::get_tag_testimonial_count_map( $terms );

		$tags = [];

		foreach ( $terms as $tag ) {
			$id = $tag->term_id;

			$tags[] = [
				'label' => $tag->name,
				'value' => (string) $id,
				'count' => $tag_testimonial_count_map[ $id ],
			];
		}

		return new \WP_REST_Response( $tags );
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
