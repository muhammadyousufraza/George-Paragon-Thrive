<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe;

use Exception;
use Stripe\Event;
use Stripe\StripeClient;
use TVA_Stripe_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Connection_V2 {

	const STRIPE_VERSION = '2023-10-16';

	const WEBHOOK_SECRET_OPTION = 'tva_stripe_webhook_secret';

	const WEBHOOK_TEST_SECRET_OPTION = 'tva_stripe_webhook_secret_test';

	const WEBHOOK_ENDPOINT_OPTION = 'tva_stripe_webhook_endpoint';

	protected static $_instance;

	protected $client;

	protected $client_test;

	protected $api_key;

	protected $is_test_mode = false;

	protected $test_key;

	protected $webhook_secret;

	protected $webhook_endpoint;

	public function __construct() {
		$this->read_credentials();
		$this->ensure_client();
	}

	/**
	 * @return Connection | Connection_V2
	 */
	public static function get_instance() {
		if ( empty( static::$_instance ) ) {
			$version = Hooks::get_stripe_version();
			if ( $version === 'v1' ) {
				static::$_instance = Connection::get_instance();
			} else {
				static::$_instance = new self();
			}
		}

		return static::$_instance;
	}

	public function get_webhook_secret() {
		if ( ! $this->webhook_secret ) {
			$this->webhook_secret = get_option( $this->is_test_mode ? static::WEBHOOK_TEST_SECRET_OPTION : static::WEBHOOK_SECRET_OPTION, '' );
		}

		return $this->webhook_secret;
	}

	public function get_test_webhook_secret() {
		return get_option( static::WEBHOOK_TEST_SECRET_OPTION, '' );
	}

	/**
	 * @return StripeClient
	 */
	public function get_client() {
		if ( ! $this->client ) {
			$this->ensure_client();
		}
		if ( ! $this->client_test ) {
			$this->ensure_test_client();
		}

		return $this->is_test_mode || ! $this->client ? $this->client_test : $this->client;
	}

	public function set_test_mode( $mode ) {
		$this->is_test_mode = $mode;
	}

	public function get_test_mode() {
		return $this->is_test_mode;
	}

	private function read_credentials() {
		$this->test_key = Credentials::get_secret_key( false ) ?: '';
		$this->api_key  = Credentials::get_secret_key() ?: '';

		if ( ! empty( $this->test_key ) && empty( $this->api_key ) ) {
			$this->is_test_mode = true;
		}
	}

	public function get_api_key() {
		if ( ! $this->api_key ) {
			$this->read_credentials();
		}

		return $this->api_key;
	}

	public function get_test_key() {
		if ( ! $this->test_key ) {
			$this->read_credentials();
		}

		return $this->test_key;
	}

	public function get_webhook_endpoint() {
		if ( ! $this->webhook_endpoint ) {
			$this->webhook_endpoint = get_option( static::WEBHOOK_ENDPOINT_OPTION, '' );
		}

		if ( ! $this->webhook_endpoint ) {
			$endpoint = uniqid( 'tva-webhook-' );
			update_option( static::WEBHOOK_ENDPOINT_OPTION, $endpoint, false );
			$this->webhook_endpoint = $endpoint;
		}

		return $this->webhook_endpoint;
	}

	/**
	 * @return StripeClient
	 */
	protected function ensure_client() {
		if ( ! $this->client ) {
			$api_key = $this->get_api_key();

			if ( $api_key ) {
				$this->client = new StripeClient( [
					'api_key'        => $api_key,
					'stripe_version' => static::STRIPE_VERSION,
				] );
			}
		}

		return $this->client;
	}

	/**
	 * @return StripeClient
	 */
	protected function ensure_test_client() {
		if ( ! $this->client_test ) {
			$test_key = $this->get_test_key();
			if ( $test_key ) {
				$this->client_test = new StripeClient( [
					'api_key'        => $test_key,
					'stripe_version' => static::STRIPE_VERSION,
				] );
			}
		}

		return $this->client_test;
	}

	public function save_webhook_secret( $secret ) {
		$this->webhook_secret = $secret;
		$option               = $this->is_test_mode ? static::WEBHOOK_TEST_SECRET_OPTION : static::WEBHOOK_SECRET_OPTION;
		update_option( $option, $secret, false );
	}

	public function get_endpoint_url() {
		$endpoint = $this->get_webhook_endpoint();

		return get_rest_url() . TVA_Stripe_Controller::$namespace . TVA_Stripe_Controller::$version . '/stripe/' . $endpoint;
	}

	public function ensure_endpoint( $live_mode = true ) {

		if ( $live_mode ) {
			$api_key = $this->get_api_key();
		} else {
			$api_key = $this->get_test_key();
		}

		$client = new StripeClient( $api_key );

		$valid = true;

		$url = $this->get_endpoint_url();

		try {
			$webhooks = $client->webhookEndpoints->all();
			$found    = false;
			foreach ( $webhooks as $webhook ) {
				if ( $webhook->url === $url ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				$webhook = $client->webhookEndpoints->create( [
					'api_version'    => static::STRIPE_VERSION,
					'url'            => $url,
					'enabled_events' => [
						Event::CHARGE_DISPUTE_CLOSED,
						Event::CHARGE_DISPUTE_CREATED,
						Event::CHARGE_SUCCEEDED,
						Event::CHECKOUT_SESSION_COMPLETED,
						Event::CHECKOUT_SESSION_ASYNC_PAYMENT_FAILED,
						Event::CHECKOUT_SESSION_ASYNC_PAYMENT_SUCCEEDED,
						Event::CUSTOMER_SUBSCRIPTION_CREATED,
						Event::CUSTOMER_SUBSCRIPTION_DELETED,
						Event::CUSTOMER_SUBSCRIPTION_UPDATED,
						Event::CUSTOMER_SUBSCRIPTION_PAUSED,
						Event::CUSTOMER_SUBSCRIPTION_RESUMED,
						Event::CUSTOMER_SUBSCRIPTION_PENDING_UPDATE_EXPIRED,
						Event::CUSTOMER_SUBSCRIPTION_PENDING_UPDATE_APPLIED,
						Event::CUSTOMER_SUBSCRIPTION_TRIAL_WILL_END,
						Event::INVOICE_CREATED,
						Event::INVOICE_DELETED,
						Event::INVOICE_FINALIZATION_FAILED,
						Event::INVOICE_FINALIZED,
						Event::INVOICE_MARKED_UNCOLLECTIBLE,
						Event::INVOICE_PAID,
						Event::INVOICE_PAYMENT_ACTION_REQUIRED,
						Event::INVOICE_PAYMENT_FAILED,
						Event::INVOICE_PAYMENT_SUCCEEDED,
						Event::INVOICE_SENT,
						Event::INVOICE_UPCOMING,
						Event::INVOICE_UPDATED,
						Event::INVOICE_VOIDED,
						Event::PAYMENT_INTENT_CANCELED,
						Event::PAYMENT_INTENT_PAYMENT_FAILED,
						Event::PAYMENT_INTENT_PROCESSING,
						Event::PAYMENT_INTENT_SUCCEEDED,
					],
				],
					[
						'stripe_account' => Credentials::get_account_id(),
					]
				);
				if ( $webhook->secret ) {
					$this->save_webhook_secret( $webhook->secret );
				}
				Request::clear_cache();
			}

		} catch ( Exception $e ) {
			$valid = false;
		}

		return $valid;
	}
}
