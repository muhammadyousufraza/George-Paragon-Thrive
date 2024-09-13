<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe;

use TVA\Product;
use TVA_Course_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Settings
 *
 * This class provides methods for handling Stripe settings.
 */
class Settings {

	const ALLOW_CHECKOUT_COUPONS_OPTIONS = 'tva_stripe_allow_checkout_coupons';

	const AUTO_DISPLAY_BUY_BUTTON_OPTIONS = 'tva_stripe_auto_display_buy_button';

	/**
	 * Update a setting.
	 *
	 * This method is responsible for updating a setting.
	 * It uses the update_option function to update the setting.
	 *
	 * @param string $option_name The name of the option.
	 * @param mixed  $value       The new value of the option.
	 */
	public static function update_setting( $option_name, $value ) {
		update_option( $option_name, $value );
	}

	/**
	 * Get a setting.
	 *
	 * This method is responsible for retrieving a setting.
	 * It uses the get_option function to retrieve the setting.
	 *
	 * @param string $option_name The name of the option.
	 * @param mixed  $default     The default value to return if the option does not exist.
	 *
	 * @return mixed The value of the option, or the default value if the option does not exist.
	 */
	public static function get_setting( $option_name, $default = null ) {
		return get_option( $option_name, $default );
	}

	/**
	 * Switch products mode from live to test and vice versa
	 *
	 * This method is responsible for switching the mode of products from live to test and vice versa.
	 * It updates the price ID, product ID, and test mode of the products.
	 *
	 * @param string $from The current mode.
	 * @param string $to   The new mode.
	 */
	public static function switch_products_mode( $from, $to ) {
		/**
		 * @var Product[] $products
		 */
		$products = Product::get_protected_products_by_integration( 'stripe' );

		foreach ( $products as $product ) {
			$rules = $product->get_rules();
			foreach ( $rules as &$rule ) {
				if ( $rule['integration'] === 'stripe' ) {
					if ( ! empty( $rule['items'][0][ $from . '_price_id' ] ) && ! empty( $rule['items'][0][ $to . '_price_id' ] ) ) {
						$rule['items'][0]['id']         = $rule['items'][0][ $to . '_price_id' ];
						$rule['items'][0]['product_id'] = $rule['items'][0][ $to . '_product_id' ];
						$rule['items'][0]['test_mode']  = $to === 'test';

						$buy_links = $product->get_buy_now_links();

						foreach ( $buy_links as &$buy_link ) {
							if ( $buy_link['integration'] === 'stripe' ) {
								$buy_link['price_id']   = $rule['items'][0][ $to . '_price_id' ];
								$buy_link['product_id'] = $rule['items'][0][ $to . '_product_id' ];
								$buy_link['test_mode']  = $to === 'test';
							}
						}
						update_term_meta( $product->get_id(), 'tva_buy_now_links', $buy_links );

						update_term_meta( $product->get_id(), 'tva_rules', $rules );
						TVA_Course_V2::delete_count_enrolled_users_cache( 0 );
						Product::delete_count_users_with_access_cache( $product->get_id() );
					}
				}
			}
		}
	}
}
