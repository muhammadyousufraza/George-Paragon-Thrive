<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Buy_Now;

use Exception;
use TVA\Stripe\Connection_V2;
use TVA\Stripe\Credentials;
use TVA\Stripe\Hooks;
use TVA\Stripe\Settings;
use function get_home_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Stripe extends Generic {

	public static $CACHE = [];


	/**
	 * @var mixed|string
	 */
	private $product_id;
	private $price_id;
	private $success_url;
	private $cancel_url;
	private $test_mode;
	private $price_type;
	private $stripe_connection;

	private $populate_email;

	private $trial     = 0;
	private $allow_coupons;
	private $reference = '';

	/**
	 * Stripe constructor.
	 *
	 * @param array $data The data associated with the Stripe payment.
	 */
	public function __construct( $data ) {
		parent::__construct( $data );
		$this->parse_data();
	}


	/**
	 * Parse the data associated with the Stripe payment.
	 *
	 * This method is responsible for parsing the data associated with the Stripe payment.
	 * It extracts the necessary information from the data and stores it in the class properties.
	 */
	protected function parse_data() {
		$this->price_id = isset( $this->data['price_id'] ) ? $this->data['price_id'] : '';
		if ( ! empty( $this->price_id ) ) {
			$this->product_id = isset( $this->data['product_id'] ) ? $this->data['product_id'] : '';
			$this->test_mode  = isset( $this->data['test_mode'] ) ? $this->data['test_mode'] : false;

			$price_type_key   = $this->test_mode ? 'test_price_type' : 'live_price_type';
			$this->price_type = isset( $this->data[ $price_type_key ] ) ? $this->data[ $price_type_key ] : '';

			$this->success_url = isset( $this->data['success_url'] ) ? $this->data['success_url'] : get_home_url();
			$this->cancel_url  = isset( $this->data['cancel_url'] ) ? $this->data['cancel_url'] : '';

			if ( ! empty( $this->success_url ) ) {
				$this->success_url = $this->append_url( $this->success_url );
			}
			if ( ! empty( $this->cancel_url ) ) {
				$this->cancel_url = $this->append_url( $this->cancel_url );
			}

			$this->populate_email = isset( $this->data['populate_email'] ) ? $this->data['populate_email'] : false;
			$this->allow_coupons  = isset( $this->data['allow_coupons'] ) ? $this->data['allow_coupons'] : Settings::get_setting( Settings::ALLOW_CHECKOUT_COUPONS_OPTIONS, false );
			$trial_key            = $this->test_mode ? 'test_free_trial' : 'live_free_trial';
			if ( $this->price_type === 'recurring' && isset( $this->data[ $trial_key ] ) && $this->data[ $trial_key ] ) {
				$this->trial = $this->data[ $this->test_mode ? 'test_free_trial_days' : 'live_free_trial_days' ];
			}

			$reference_key = $this->test_mode ? 'test_reference' : 'live_reference';
			if ( isset( $this->data[ $reference_key ] ) && $this->data[ $reference_key ] ) {
				$this->reference = $this->get_client_reference( $this->data[ $this->test_mode ? 'test_reference_type' : 'live_reference_type' ] );
			}

			$stripe = Connection_V2::get_instance();
			$stripe->set_test_mode( $this->test_mode );
			$this->stripe_connection = $stripe->get_client();
		}
	}

	/**
	 * Check if the Stripe payment is valid.
	 *
	 * This method checks if the price ID associated with the Stripe payment is not empty.
	 *
	 * @return bool True if the price ID is not empty, false otherwise.
	 */
	public function is_valid() {
		return ! empty( $this->price_id );
	}

	/**
	 * Get the URL for the checkout session
	 *
	 * This method is responsible for generating the URL for the checkout session.
	 * It creates a checkout session with the Stripe API and returns the URL for the session.
	 *
	 * @return string The URL for the checkout session.
	 */
	public function get_url() {
		if ( $this->price_id && ! isset( static::$CACHE[ $this->price_id ] ) ) {
			$checkout_data = [
				'mode'       => $this->price_type === 'recurring' ? 'subscription' : 'payment',
				'line_items' => [
					[
						'price'    => $this->price_id,
						'quantity' => 1,
					],
				],
			];

			if ( ! empty( $this->success_url ) ) {
				$checkout_data['success_url'] = $this->success_url;
			}

			if ( ! empty( $this->cancel_url ) ) {
				$checkout_data['cancel_url'] = $this->cancel_url;
			}

			if ( $this->populate_email && is_user_logged_in() ) {
				$checkout_data['customer_email'] = wp_get_current_user()->user_email;
			}

			if ( $this->allow_coupons ) {
				$checkout_data['allow_promotion_codes'] = true;
			}

			if ( ! empty( $this->reference ) ) {
				$checkout_data['client_reference_id'] = $this->reference;
			}

			if ( ! empty( $this->trial ) ) {
				$checkout_data['subscription_data'] = [
					'trial_period_days' => $this->trial,
					'trial_settings'    => [
						'end_behavior' => [
							'missing_payment_method' => 'cancel',
						],
					],
				];
			}

			/**
			 * Filter the Checkout Data.
			 *
			 * This allows developers to add additional metadata
			 * to the checkout data for the Buy Now button for Stripe.
			 *
			 * Example:
			 *
			 *    add_filter(
			 *        'tva_buy_now_url_stripe_checkout_data',
			 *        function( array $checkout_data, array $buynow_data ) : array {
			 *            return array_merge(
			 *                $checkout_data,
			 *
			 *                // Additional data to pass along with the checkout data...
			 *                array(
			 *
			 *                    // Add Metadata...
			 *                    'payment_intent_data' => array(
			 *                      'metadata' => array_merge(
			 *
			 *                          // Make sure and include any previously added metadata...
			 *                          $checkout_data['metadata'] ?? array(),
			 *
			 *                          // Add our metadata...
			 *                          array(
			 *                              'product_id'      => $buynow_data['product_id'] ?? ''
			 *                              'my_metadata_key' => 'my_metadata_value',
			 *                          )
			 *                      ),
			 *                    ),
			 *                )
			 *            );
			 *        },
			 *        10, 2
			 *    );
			 *
			 * @param array $checkout_data Checkout data used to generate the Buy Now button that leads to Stripe checkout.
			 * @param array $buynow_data   Additional data about the Buy Now button.
			 *
			 * @var array
			 */
			$checkout_data = apply_filters( 'tva_buy_now_url_stripe_checkout_data', $checkout_data, $this->data );

			try {
				static::$CACHE[ $this->price_id ] = $this->stripe_connection->checkout->sessions->create( $checkout_data, [
					'stripe_account' => Credentials::get_account_id(),
				] )->url;
			} catch ( Exception $e ) {

			}
		}

		return isset( static::$CACHE[ $this->price_id ] ) ? static::$CACHE[ $this->price_id ] : '';
	}

	/**
	 * Get the client reference
	 *
	 * @param $type
	 *
	 * @return mixed|string
	 */
	public function get_client_reference( $type ) {
		if ( is_user_logged_in() ) {
			switch ( $type ) {
				case 'user_id':
					return get_user_meta( get_current_user_id(), Hooks::CUSTOMER_META_ID, true );
				case 'user_name':
					return wp_get_current_user()->display_name;
				default:
					return '';
			}
		}

		return '';
	}

	/**
	 * Append the price_id and product_id to the URL
	 *
	 * This method is responsible for appending the price ID and product ID to the URL.
	 * It uses the add_query_arg function to append the parameters to the URL.
	 *
	 * @param $url - The URL to which the parameters should be appended.
	 *
	 * @return string The URL with the parameters appended.
	 */
	protected function append_url( $url ) {
		return add_query_arg( [
			'price_id'   => $this->price_id,
			'product_id' => $this->product_id,
		], $url );
	}
}
