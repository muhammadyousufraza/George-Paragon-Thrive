<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe;

use Exception;
use TVA\Product;
use TVA_Const;
use TVA_Order;
use function add_action;
use function add_filter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * This class provides methods for handling Stripe hooks.
 */
class Hooks {

	/**
	 * Constant for the Stripe customer ID meta key
	 */
	const CUSTOMER_META_ID = 'tva_stripe_customer_id';

	/**
	 * Constant for the Stripe version meta key
	 */
	const STRIPE_VERSION_META = 'tva_stripe_version';

	/**
	 * Constant for the Stripe live count meta key of the number of protected products
	 */
	const STRIPE_LIVE_COUNT = 'tva_stripe_live_count';

	/**
	 * Constant for the Stripe test count meta key of the number of protected products
	 */
	const STRIPE_TEST_COUNT = 'tva_stripe_test_count';

	/**
	 * Initialize the hooks.
	 *
	 * This method is responsible for initializing the hooks.
	 * It adds the necessary actions and filters.
	 */
	public static function init() {
		static::add_actions();
		static::add_filters();
	}

	/**
	 * Add actions.
	 *
	 * This method is responsible for adding the necessary actions.
	 * It uses the add_action function to add the actions.
	 */
	public static function add_actions() {
		add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ], 1 );
	}

	/**
	 * Add filters.
	 *
	 * This method is responsible for adding the necessary filters.
	 * It uses the add_filter function to add the filters.
	 */
	public static function add_filters() {
		add_filter( 'tva_admin_localize', [ __CLASS__, 'admin_localize' ] );
	}

	/**
	 * Get the Stripe version.
	 * v1 - for the old enrollment method
	 * v2 - current valid version
	 *
	 *
	 * @return false|mixed|null
	 */
	public static function get_stripe_version() {
		return get_option( static::STRIPE_VERSION_META, '' );
	}

	public static function v2_update() {
		static::ensure_customer_ids();
		static::check_v1();
		static::count_protected_products();
	}

	/**
	 * Update the Stripe version to v1 if the API key or test key exist
	 *
	 * @return void
	 */
	public static function check_v1() {
		$v1_connection = Connection::get_instance();
		if ( $v1_connection->get_api_key() || $v1_connection->get_test_key() ) {
			update_option( static::STRIPE_VERSION_META, 'v1' );
		}
	}

	/**
	 * Check if the Stripe version is v1 (legacy)
	 *
	 * @return bool
	 */
	public static function is_legacy() {
		return static::get_stripe_version() === 'v1';
	}

	/**
	 * Fetch the customer IDs for all orders and update the user meta
	 * Customer IDs are used to identify the customer in the Stripe dashboard
	 *
	 */
	public static function ensure_customer_ids() {
		$orders = TVA_Order::get_orders_by_gateway( TVA_Const::STRIPE_GATEWAY );
		foreach ( $orders as $order ) {
			$user_id = $order['user_id'];
			if ( empty( get_user_meta( $user_id, static::CUSTOMER_META_ID, true ) ) ) {
				$user = get_user_by( 'id', $user_id );
				try {
					$customer_id = Request::get_customer_id( $user->user_email );
					if ( $customer_id ) {
						update_user_meta( $user_id, static::CUSTOMER_META_ID, $customer_id );
					}
				} catch ( Exception $e ) {
					//do nothing
				}
			}
		}
	}

	/**
	 * Get the number of protected products that are using the Stripe integration
	 *
	 * @param $live
	 *
	 * @return false|mixed|null
	 */
	public static function get_protected_products_count( $live = true ) {
		return $live ? get_option( static::STRIPE_LIVE_COUNT, 0 ) : get_option( static::STRIPE_TEST_COUNT, 0 );
	}

	/**
	 * Count the number of protected products that are using the Stripe integration
	 *
	 * @return void
	 */
	public static function count_protected_products() {
		/**
		 * @var Product[] $products
		 */
		$products   = Product::get_protected_products_by_integration( 'stripe' );
		$live_count = 0;
		$test_count = 0;
		foreach ( $products as $product ) {
			$rules = $product->get_rules();
			foreach ( $rules as &$rule ) {
				if ( $rule['integration'] === 'stripe' && isset( $rule['items'][0]['test_mode'] ) ) {
					if ( $rule['items'][0]['test_mode'] ) {
						$test_count ++;
					} else {
						$live_count ++;
					}
				}
			}
		}
		update_option( static::STRIPE_LIVE_COUNT, $live_count );
		update_option( static::STRIPE_TEST_COUNT, $test_count );
	}

	/**
	 * Stripe details for the admin localize script
	 *
	 * @param $data
	 *
	 * @return array|mixed
	 */
	public static function admin_localize( $data = [] ) {
		$connection = Connection_V2::get_instance();

		$data['stripe'] = [
			'api_key'                                 => $connection->get_api_key(),
			'test_api_key'                            => $connection->get_test_key(),
			'account_id'                              => Credentials::get_account_id(),
			'is_legacy'                               => static::is_legacy(),
			Settings::ALLOW_CHECKOUT_COUPONS_OPTIONS  => Settings::get_setting( Settings::ALLOW_CHECKOUT_COUPONS_OPTIONS, false ),
			Settings::AUTO_DISPLAY_BUY_BUTTON_OPTIONS => Settings::get_setting( Settings::AUTO_DISPLAY_BUY_BUTTON_OPTIONS, false ),
		];

		return $data;
	}

	public static function admin_notices() {
		if ( static::is_legacy() ) {
			include TVA_Const::plugin_path( 'admin/includes/templates/stripe/v1-admin-notice.php' );
		}
	}
}
