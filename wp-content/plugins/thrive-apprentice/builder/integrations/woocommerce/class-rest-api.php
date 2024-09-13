<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Rest_Api
 * @package Thrive\Theme\Integrations\WooCommerce
 */
class Rest_Api {
	public static $namespace = TTB_REST_NAMESPACE;
	public static $route     = '/woo';

	const ALLOWED_FUNCTIONS = [
		'woocommerce_related_products',
		'woocommerce_upsell_display',
		'woocommerce_content',
	];

	public static function register_routes() {
		register_rest_route( self::$namespace, self::$route . '/render', [
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'render' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );
	}

	/**
	 * Render WooCommerce functions
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function render( $request ) {

		$fn      = $request->get_param( 'fn' );
		$content = '';

		if ( in_array( $fn, static::ALLOWED_FUNCTIONS, true ) ) {

			$args          = $request->get_param( 'args' );
			$output_buffer = ! empty( $request->get_param( 'output_buffer' ) );

			$args = static::before_function_call( $fn, $args, $request );

			if ( $output_buffer ) {
				ob_start();
				/* woocommerce_content() has no $args parameter, and in PHP 8 it breaks if we send args to the function */
				if ( $fn === 'woocommerce_content' ) {
					call_user_func( $fn );
				} else {
					call_user_func_array( $fn, $args );
				}
				$content = ob_get_clean();
			} else {
				if ( $fn === 'woocommerce_content' ) {
					$content = call_user_func( $fn );
				} else {
					$content = call_user_func_array( $fn, $args );
				}
			}
		}

		return new \WP_REST_Response( [ 'content' => $content ], 200 );
	}

	/**
	 * Actions to be done before the call of a specific function
	 *
	 * @param String           $fn
	 * @param array            $args
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	private static function before_function_call( $fn, $args, $request ) {

		$query_vars = $request->get_param( 'query_vars' );

		switch ( $fn ) {
			case 'woocommerce_related_products':
				$args = [ $args ];
				if ( ! empty( $query_vars['page_id'] ) ) {
					delete_transient( 'wc_related_' . $query_vars['page_id'] );
				}
				break;
			case 'woocommerce_content':
				Shortcodes\Shop_Template::before_render( $args );
				$query_vars['posts_per_page'] = $args['posts_per_page'];
				break;
			case 'woocommerce_upsell_display':
				$args = [ $args['posts_per_page'], $args['columns'], $args['orderby'], $args['order'] ];
				break;
		}

		\TCB\Integrations\WooCommerce\Main::init_frontend_woo_functionality();

		\Thrive_Utils::set_query_vars( $query_vars );

		return $args;
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public static function route_permission( $request ) {
		return \Thrive_Theme_Product::has_access();
	}
}
