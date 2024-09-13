<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe;

use Stripe\Exception\ApiErrorException;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Request {
	const PRODUCTS_OPTION_TEST = 'tva_stripe_products_test';

	const PRODUCTS_OPTION = 'tva_stripe_products';

	/**
	 * Get all products from Stripe and cache them
	 *
	 * This method retrieves all products from Stripe and caches them.
	 * If the cache is empty or the force parameter is true, it fetches the products from Stripe.
	 *
	 * @param bool $force     Whether to force fetching the products from Stripe.
	 * @param bool $test_mode Whether to use test mode.
	 *
	 * @return array The products from Stripe.
	 */
	public static function get_all_products( $force = false, $test_mode = false ) {
		$option_name = $test_mode ? static::PRODUCTS_OPTION_TEST : static::PRODUCTS_OPTION;
		$products    = get_option( $option_name, [] );
		if ( empty( $products ) || $force ) {
			$products   = [];
			$connection = Connection_V2::get_instance();
			$connection->set_test_mode( $test_mode );
			$product_client = $connection->get_client()->products;
			static::fetch_all( $product_client, $products );

			//make sure that we don't save classes in the option
			$products = array_map( function ( $product ) {
				$product = $product->jsonSerialize();

				//unset not needed fields
				unset( $product['metadata'], $product['attributes'], $product['object'], $product['images'], $product['package_dimensions'], $product['shippable'] );

				return $product;
			}, $products );

			update_option( $option_name, $products, false );
		}

		return $products;
	}

	/**
	 * Clear the cache for the products
	 *
	 * @return void
	 */
	public static function clear_cache() {
		delete_option( static::PRODUCTS_OPTION );
		delete_option( static::PRODUCTS_OPTION_TEST );
	}

	/**
	 * Get an invoice.
	 *
	 * This method retrieves an invoice from Stripe.
	 *
	 * @param string $invoice_id The ID of the invoice.
	 *
	 * @return object The invoice from Stripe.
	 * @throws ApiErrorException
	 */
	public static function get_invoice( $invoice_id ) {
		return Connection_V2::get_instance()->get_client()->invoices->retrieve( $invoice_id );
	}

	/**
	 * Get a customer ID.
	 *
	 * This method retrieves a customer ID from Stripe.
	 *
	 * @param string $email The email of the customer.
	 *
	 * @return string|false The customer ID, or false if the customer does not exist.
	 * @throws ApiErrorException
	 */
	public static function get_customer_id( $email ) {
		$connection = Connection_V2::get_instance();
		$customer   = $connection->get_client()->customers->search( [
			'query' => "email:'$email'",
		] );

		if ( empty( $customer->data ) ) {
			return false;
		}

		return $customer->data[0]->id;
	}

	/**
	 * Get default filters.
	 *
	 * This method returns the default filters for fetching objects from Stripe.
	 *
	 * @return array The default filters.
	 */
	public static function get_default_filters() {
		return [
			'limit' => 100,
		];
	}

	/**
	 * Generic function to fetch all objects from Stripe
	 *
	 * This method fetches all objects of a certain type from Stripe.
	 *
	 * @param object $client  The Stripe client.
	 * @param array  $list    The list to which the fetched objects should be added.
	 * @param array  $filters The filters to use when fetching the objects.
	 */
	public static function fetch_all( $client, &$list = [], $filters = [] ) {
		$filters = array_merge( static::get_default_filters(), $filters );
		$filters = array_filter( $filters );

		$response = $client->all( $filters, [
			'stripe_account' => Credentials::get_account_id(),
		] );

		$list = array_merge( $list, $response->data );

		if ( $response->has_more ) {
			$filters['starting_after'] = $response->data[ count( $response->data ) - 1 ]->id;
			static::fetch_all( $client, $list, $filters );
		}
	}

	/**
	 * Get all prices for a product.
	 *
	 * This method retrieves all prices for a product from Stripe.
	 *
	 * @param string $product_id The ID of the product.
	 * @param bool   $test_mode  Whether to use test mode.
	 *
	 * @return WP_Error|array The prices for the product, or a WP_Error object if an error occurred.
	 */
	public static function get_product_prices( $product_id, $test_mode = false ) {
		$prices     = [];
		$connection = Connection_V2::get_instance();
		$connection->set_test_mode( $test_mode );
		$price_client = $connection->get_client()->prices;
		try {
			static::fetch_all( $price_client, $prices, [ 'product' => $product_id ] );
		} catch ( ApiErrorException $e ) {
			return new WP_Error( 'stripe_error', $e->getMessage() );
		}

		return $prices;
	}

	/**
	 * Get the URL for the customer portal.
	 *
	 * This method retrieves the URL for the customer portal from Stripe.
	 *
	 * @return string The URL for the customer portal.
	 */
	public static function get_customer_portal() {
		$url    = '';
		$client = Connection_V2::get_instance()->get_client();
		if ( $client ) {
			$user = wp_get_current_user();

			$user_stripe_id = get_user_meta( $user->ID, Hooks::CUSTOMER_META_ID, true );
			if ( $user_stripe_id ) {

				try {
					$session = $client->billingPortal->sessions->create( [
						'customer'   => $user_stripe_id,
						'return_url' => get_permalink(),
					] );
					$url     = $session->url;
				} catch ( ApiErrorException $e ) {
					$message = $e->getMessage();
					if ( strpos( $message, 'a similar object exists in test mode' ) !== false ) {
						Connection_V2::get_instance()->set_test_mode( true );
						$client = Connection_V2::get_instance()->get_client();
						try {
							$session = $client->billingPortal->sessions->create( [
								'customer'   => $user_stripe_id,
								'return_url' => get_permalink(),
							] );
							$url     = $session->url;
						} catch ( ApiErrorException $e ) {
						}
					}
				}
			}
		}

		return $url;
	}

}
