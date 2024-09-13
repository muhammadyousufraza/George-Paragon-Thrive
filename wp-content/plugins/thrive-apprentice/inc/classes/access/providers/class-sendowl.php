<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Access\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Sendowl extends Base {
	/**
	 * @var string
	 */
	const KEY = 'sendowl_product';

	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		 * Activate general hooks
		 */
		parent::__construct();

		add_action( 'tva_products_sendowl_bundle_integration_add_access', [ $this, 'product_added_access' ], 10, 2 );
		add_action( 'tva_products_sendowl_bundle_integration_removed_access', [ $this, 'product_removed_access' ], 10, 2 );
	}

	/**
	 * @param array $levels
	 *
	 * @return array
	 */
	public function get_users_with_access( $levels = [] ) {
		$args = array(
			'product_id' => $levels,
			'limit'      => PHP_INT_MAX,
		);

		$customers = \TVA_Customer::get_list( $args );
		$ids       = [];

		foreach ( $customers as $customer ) {
			$ids [] = $customer->get_id();
		}

		return $ids;
	}

	public static function is_active() {
		return \TVA_SendOwl::is_connected();
	}
}
