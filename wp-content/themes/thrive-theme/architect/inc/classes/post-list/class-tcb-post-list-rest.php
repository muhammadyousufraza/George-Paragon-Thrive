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
 * Class TCB_Post_List_REST
 */
class TCB_Post_List_REST {

	public static $namespace = 'tcb/v1';
	public static $route = '/posts';

	public function __construct() {
		$this->register_routes();
	}

	public function register_routes() {
		register_rest_route( static::$namespace, static::$route, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_posts' ],
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/html', array(
			array(
				/* This should be READABLE, but a lot of data is sent through this request, and it is appended in the request URL string.
				 * Because of the really long URL string, there were 414 errors for some users because the server can block requests like these.
				 * As a solution, we changed this to CREATABLE ( POST ) so the data is added inside the request */
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_html' ],
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/terms', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_terms' ],
				'permission_callback' => [ $this, 'route_permission' ],
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/authors', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_authors' ],
				'permission_callback' => [ $this, 'route_permission' ],
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/taxonomies', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_taxonomies' ],
				'permission_callback' => [ $this, 'route_permission' ],
			),
		) );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function route_permission( $request ) {
		$post_id = isset( $request['post_id'] ) ? $request['post_id'] : null;

		return \TCB_Product::has_external_access( $post_id );
	}

	/**
	 * Calculate the number of search results that we should return: 3 times the number of searched characters, minimum 20, max. 100.
	 *
	 * @param $search - should be a string, if it's not, we return a fixed number.
	 *
	 * @return int
	 */
	private static function get_results_count( $search = '' ) {
		if ( is_string( $search ) ) {
			$count = min( 100, max( 20, strlen( $search ) * 3 ) );
		} else {
			$count = 20;
		}

		return $count;
	}

	/**
	 * Get terms from taxonomy
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_terms( $request ) {

		$taxonomy = $request->get_param( 'taxonomy' );
		$search   = $request->get_param( 'search' );

		$terms = [];

		if ( ! empty( $taxonomy ) ) {

			$args = array(
				'number'     => static::get_results_count( $search ),
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			);

			if ( ! empty( $search ) ) {
				$args['search'] = $search;
			}

			$specific = $request->get_param( 'specific' );
			if ( ! empty( $specific ) ) {
				$args = array(
					'number'     => 0,
					'hide_empty' => false,
					'include'    => $request->get_param( 'terms' ),
				);
			}

			$all = get_terms( $args );

			$terms = array_map( function ( $item ) {
				return [
					'value' => $item->term_id,
					'label' => $item->name,
				];
			}, $all );
		}

		$terms = array_values( $terms );

		return new WP_REST_Response( $terms );
	}

	/**
	 * Get terms from taxonomy
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_posts( $request ) {
		$post_type = $request->get_param( 'post_type' );
		$search    = $request->get_param( 'search' );

		$args = array(
			'posts_per_page' => static::get_results_count( $search ),
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_type'      => $post_type,
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		/* this is for when we just want some specific results returned */
		$specific = $request->get_param( 'specific' );
		if ( ! empty( $specific ) ) {
			$args = array(
				'number'    => 0,
				'include'   => $request->get_param( 'terms' ),
				'post_type' => $post_type,
			);
		}

		$all = get_posts( $args );

		$all = apply_filters( 'tcb_filter_rest_products', $all, $request );

		$posts = array_map( function ( $item ) {
			return [
				'value' => $item->ID,
				'label' => $item->post_title,
			];
		}, $all );

		$posts = array_values( $posts );

		return new WP_REST_Response( $posts );

	}

	/**
	 * Get authors of the blog
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_authors( $request ) {

		$search = $request->get_param( 'search' );

		$args = array( 'number' => static::get_results_count( $search ) );

		if ( ! empty( $search ) ) {
			$args['search'] = '*' . $search . '*';
		}
		/* this is for when we just want some specific results returned */
		$specific = $request->get_param( 'specific' );
		if ( ! empty( $specific ) ) {
			$args = array(
				'number'  => 0,
				'include' => $request->get_param( 'terms' ),
			);
		}

		$all = get_users( $args );

		$authors = array_map( function ( $item ) {
			return [
				'value' => $item->ID,
				'label' => $item->display_name,
			];
		}, $all );

		$authors = array_values( $authors );

		return new WP_REST_Response( $authors );
	}

	/**
	 * Get post type taxonomies
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_taxonomies( $request ) {

		$post_type = $request->get_param( 'post_type' );

		if ( empty( $post_type ) ) {
			$taxonomies = [];
		} else {
			$all = get_object_taxonomies( $post_type, 'object' );

			$taxonomies = array_map( function ( $item ) {
				return [
					'value' => $item->name,
					'label' => $item->label,
				];
			}, $all );

			$taxonomies = array_filter( $taxonomies, function ( $taxonomy ) {
				$terms = get_terms( [
					'taxonomy'   => $taxonomy['value'],
					'hide_empty' => false,
				] );

				/* we only return taxonomies that have terms inside them */

				return count( $terms ) > 0;
			} );
		}

		$taxonomies = array_values( $taxonomies );

		return new WP_REST_Response( $taxonomies );
	}

	/**
	 * Get posts filtered by args for the post list
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_html( $request ) {
		/* if we send a template parameter, we're going to print the post list after that one */
		$content = $request->get_param( 'content' );

		if ( ! empty( $content ) ) {
			$content = str_replace( [ '{({', '})}' ], [ '[', ']' ], $content );
		}

		$args = $request->get_param( 'args' );

		$args = array_merge(
			array(
				'attr'       => [
					'total_sticky_count' => 0,
				],
				'query'      => [],
				'identifier' => '',
			),
			empty( $args ) ? [] : $args
		);

		$total_posts = ! empty( $args['query']['posts_per_page'] ) ? $args['query']['posts_per_page'] : 10;

		/* if the 'get_initial_posts' flag is not active, get the posts normally */
		if ( empty( $args['query']['get_initial_posts'] ) ) {

			if ( ! isset( $args['query']['rules'] ) ) {
				$args['query']['rules'] = [];
			}

			$posts_per_page = (int) $args['query']['posts_per_page'];

			if ( ! empty( $args['query']['sticky'] ) ) {
				$query_args_sticky = TCB_Post_List::prepare_wp_query_args_sticky( $args['query'] );
				$sticky_posts      = get_posts( $query_args_sticky );
				/*sticky posts from the current page*/
				$number_of_sticky_posts = count( $sticky_posts );

				if ( (int) $args['query']['posts_per_page'] !== - 1 ) {
					if ( $number_of_sticky_posts > 0 ) {
						/*compute the number of 'normal' posts that needs to be added on a page with sticky posts*/
						$args['query']['posts_per_page'] -= $number_of_sticky_posts;
						/*on a page with sticky posts the first 'normal' post will always have the offset 1*/
						$args['query']['offset'] = 1;
					} else {
						/*compute the offset of the first normal post displayed on a page with only normal posts*/
						$args['query']['offset'] = $args['query']['posts_per_page'] * ( $query_args_sticky['paged'] - 1 ) - $args['attr']['total_sticky_count'] + 1;
					}
				}
			} else {
				$sticky_posts           = [];
				$number_of_sticky_posts = 0;
			}

			/*if we need to add 'normal' posts on a page with sticky posts*/
			if ( $posts_per_page !== $number_of_sticky_posts ) {
				$query_args = TCB_Post_List::prepare_wp_query_args( $args['query'] );

				$filters = ! empty( $args['filters'] ) ? $args['filters'] : [];

				$query_args = TCB_Post_List_Filter::filter( $query_args, $filters );

				$query       = new WP_Query( $query_args );
				$posts       = $query->posts;
				$total_posts = $query->found_posts;
				$posts       = array_merge( $sticky_posts, $posts );
			} else {
				$posts = $sticky_posts;
			}
		} else {
			/* if the flag is active, use the default query to get the post info we need */
			$post_ids = empty( $args['query']['post_ids'] ) ? [] : $args['query']['post_ids'];
			$posts    = $this->get_existing_posts( $post_ids );
		}

		global $post;

		TCB_Post_List::enter_post_list_render();

		$post_list = new TCB_Post_List( $args['attr'], $content );

		$results = [];

		foreach ( $posts as $key => $post ) {
			if ( empty( $content ) ) {
				/* posts are sent as key - value pairs, because it's easier to find them, but we send a parameter of order so we know how to display them */
				$results[ get_the_ID() ] = TCB_Post_List::post_info( $key + 1 );
			} else {
				$results[ $key + 1 ] = $post_list->article_content();
			}
		}

		TCB_Post_List::exit_post_list_render();

		return new WP_REST_Response( array(
			'total_post_count' => $total_posts,
			'posts'            => $results,
			'count'            => count( $results ),
		) );
	}

	/**
	 * Get the first 7 posts and all the posts that exist in the current page.
	 *
	 * @param $post_ids
	 *
	 * @return array
	 */
	public function get_existing_posts( $post_ids ) {
		$default_query = TCB_Post_List::get_default_query();

		$default_query['offset']         = 0;
		$default_query['posts_per_page'] = 7;

		/* get the first 7 posts (  6 + 1 to take into account excluding current post )*/
		$first_posts = get_posts( $default_query );

		/* no posts? get pages instead */
		if ( empty( $first_posts ) ) {
			$default_query['post_type'] = 'page';

			$first_posts = get_posts( $default_query );
		}

		if ( ! empty( $post_ids ) ) {
			$existing_posts_query = array(
				'posts_per_page' => count( $post_ids ),
				'post__in'       => $post_ids,
				'post_status'    => 'any',
				/* these can also be pages or custom post types */
				'post_type'      => 'any',
			);

			$extra_posts = get_posts( $existing_posts_query );

			$extra_posts = apply_filters( 'tcb_localize_existing_post_list', $extra_posts, $post_ids );

			/* also get the posts that are already in the page ( we have their IDs in 'get_initial_posts' ) */
			$first_posts = array_merge( $first_posts, $extra_posts );
		}

		return $first_posts;
	}
}

new TCB_Post_List_REST();
