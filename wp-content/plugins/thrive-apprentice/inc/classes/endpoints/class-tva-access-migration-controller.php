<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVA_Access_Migration_Controller extends WP_REST_Controller {
	protected $rest_base = 'access_migration';
	//TODO create a constant for this
	protected $namespace = 'tva/v1';

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'migrate_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(
					'id' => array(
						'description' => 'ID of the product',
						'type'        => 'integer',
						'required'    => true,
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );
	}

	public function migrate_item( $request ) {
		ini_set( 'memory_limit', TVE_EXTENDED_MEMORY_LIMIT );
		ignore_user_abort( true );

		$product_id = (int) $request->get_param( 'id' );
		$product    = new \TVA\Product( $product_id );

		/**
		 * Security check if product is valid
		 */
		if ( ! empty( $product->get_id() ) ) {
			\TVA\Access\Migration::migrate_access_for_product( $product );
		}

		if ( \TVA\Access\Migration::is_done() ) {
			\TVA\Access\Migration::mark_migration_done();
		}

		return new WP_REST_Response( [], 200 );
	}

	/**
	 * Endpoint to get all products
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$products   = TVA\Product::get_items();
		$open_modal = 1;
		if ( count( $products ) === 0 ) {
			/**
			 * Security check
			 */
			\TVA\Access\Migration::mark_migration_done();
			$open_modal = 0;
		}

		return new WP_REST_Response( [
			'open_modal'  => $open_modal,
			'spinner_url' => tve_editor_url() . '/editor/css/images/loading-spinner.gif',
			'items'       => $products,
		], 200 );
	}
}
