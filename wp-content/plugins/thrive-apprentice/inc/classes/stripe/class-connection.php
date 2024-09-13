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
use Thrive_Dash_List_Manager;
use TVA_Stripe_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Connection
 *
 * @deprecated - no longer used unless the user is on an old version of Stripe integration
 */
class Connection {

	const ACCOUNT_OPTION = 'tva_stripe_account_id';

	const WEBHOOK_SECRET_OPTION = 'tva_stripe_webhook_secret';

	const WEBHOOK_TEST_SECRET_OPTION = 'tva_stripe_webhook_secret_test';

	const WEBHOOK_ENDPOINT_OPTION = 'tva_stripe_webhook_endpoint';

	protected static $_instance;

	protected $client;

	protected $client_test;

	protected $api_key;

	protected $is_test_mode = false;

	protected $test_key;

	protected $account_id;

	protected $webhook_secret;

	protected $webhook_endpoint;

	protected $dash_api_connection;

	public function __construct() {
		$this->read_credentials();
		$this->ensure_client();
	}

	/**
	 * @return Connection
	 */
	public static function get_instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new self();
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

	public function get_dash_api_connection() {
		if ( ! isset( $this->dash_api_connection ) ) {
			$this->dash_api_connection = Thrive_Dash_List_Manager::connection_instance( 'stripe' );
		}

		return $this->dash_api_connection;
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

	public function get_account_id() {
		if ( ! $this->account_id ) {
			$this->account_id = get_option( static::ACCOUNT_OPTION, '' );
		}

		return $this->account_id;
	}

	private function read_credentials() {
		$dash_api_connection = $this->get_dash_api_connection();
		if ( $dash_api_connection ) {
			$credentials = $dash_api_connection->get_credentials();

			$this->test_key = isset( $credentials['test_key'] ) ? $credentials['test_key'] : '';
			$this->api_key  = isset( $credentials['api_key'] ) ? $credentials['api_key'] : '';

			if ( ! empty( $this->test_key ) && empty( $this->api_key ) ) {
				$this->is_test_mode = true;
			}
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
				$this->client = new StripeClient( $api_key );
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
				$this->client_test = new StripeClient( $test_key );
			}
		}

		return $this->client_test;
	}

	public function save_account_id( $id ) {
		$this->account_id = $id;
		update_option( static::ACCOUNT_OPTION, $id, false );
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

	public function ensure_endpoint() {
		$this->read_credentials();

		$api_key = $this->get_api_key();
		if ( ! $api_key || $this->is_test_mode ) {
			$api_key = $this->get_test_key();
		}

		$client = new StripeClient( $api_key );

		$valid   = true;
		$message = '';

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
						'stripe_account' => $this->get_account_id(),
					]
				);
				if ( $webhook->secret ) {
					$this->save_webhook_secret( $webhook->secret );
				}
				Request::clear_cache();
			}

		} catch ( Exception $e ) {
			$message = __( 'Your API key is invalid, please ensure that you are using the keys from your Thrive Themes connected account.', 'thrive-apprentice' );
			$valid   = false;
		}

		return [
			'success' => $valid,
			'message' => $message,
		];
	}
}
