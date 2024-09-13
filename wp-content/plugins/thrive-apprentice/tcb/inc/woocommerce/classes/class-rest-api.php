<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Rest_Api
 *
 * @package Thrive\Theme\Integrations\WooCommerce
 */
class Rest_Api {
	public static $namespace = 'tcb/v1';
	public static $route = '/woo';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route . '/render_shop', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'render_shop' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/render_product_categories', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'render_product_categories' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/variations', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_product_variations' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'product_id'   => [
						'type'              => 'int',
						'required'          => false,
						'validate_callback' => static function ( $param ) {
							return ! empty ( $param );
						},
					],
					'variation_id' => [
						'type'              => 'int',
						'required'          => false,
						'validate_callback' => static function ( $param ) {
							return ! empty ( $param );
						},
					],

				],
			],
		] );
	}

	/**
	 * Render the WooCommerce shop element
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function render_shop( $request ) {
		$args = $request->get_param( 'args' );

		Main::init_frontend_woo_functionality();

		$content = Shortcodes\Shop\Main::render( $args );

		return new \WP_REST_Response( [ 'content' => $content ], 200 );
	}

	/**
	 * Render the WooCommerce product categories element
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function render_product_categories( $request ) {
		$args = $request->get_param( 'args' );

		$content = Shortcodes\Product_Categories\Main::render( $args );

		return new \WP_REST_Response( [ 'content' => $content ], 200 );
	}

	/**
	 * Check if a given request has access to this route
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public static function route_permission( $request ) {
		return \TCB_Product::has_external_access();
	}

	/**
	 * Get the variations of a product
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_product_variations( $request ) {
		$variation_id = $request->get_param( 'variation_id' );
		$product_id   = $request->get_param( 'product_id' );
		$product      = wc_get_product( $product_id );

		//$product will be false in case the $product_id is not a valid product id
		if ( $product && ( $product->is_type( 'variable' ) || $product->is_type( 'variable-subscription' ) ) ) {
			$available_variations = $product->get_available_variations();
			if ( $variation_id ) {
				$selected_variation = array_filter(
					$available_variations,
					function ( $value ) use ( $variation_id ) {
						return $value['variation_id'] === (int) $variation_id;
					}
				);
				$variations         = $selected_variation[1];
			} else {
				$variations = $available_variations;
			}
		} else {
			$variations = [];
		}

		return new \WP_REST_Response( [ 'variation' => $variations ], 200 );
	}
}
