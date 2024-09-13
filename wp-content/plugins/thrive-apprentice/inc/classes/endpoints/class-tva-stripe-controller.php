<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use Random\RandomException;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use TVA\Stripe\Connection_V2;
use TVA\Stripe\Credentials;
use TVA\Stripe\Events\Generic;
use TVA\Stripe\Request;
use TVA\Stripe\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVA_Stripe_Controller extends TVA_REST_Controller {

	public $base = 'stripe';

	const THRIVE_KEY = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

	const API_URL = 'https://service-api.thrivethemes.com/stripe/connect_ouath';

//	const API_URL = 'http://localhost/services-api.thrivethemes.com/stripe/connect_ouath';

	public function register_routes() {
		$stripe_connection    = Connection_V2::get_instance();
		$webhook_endpoint     = $stripe_connection->get_webhook_endpoint();
		$credentials_endpoint = Credentials::get_credentials_endpoint();

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/connect_account', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_connect_account_link' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/disconnect', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'disconnect' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/create_page', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_page' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'title' => [
						'required' => true,
						'type'     => 'string',
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/' . $webhook_endpoint, [
			[
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => [ $this, 'generic_listen' ],
				'permission_callback' => '__return_true',
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/' . $credentials_endpoint, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_credentials' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'state'                => [
						'required' => true,
						'type'     => 'string',
					],
					'secret_key'           => [
						'required' => true,
						'type'     => 'string',
					],
					'test_secret_key'      => [
						'required' => true,
						'type'     => 'string',
					],
					'publishable_key'      => [
						'required' => true,
						'type'     => 'string',
					],
					'test_publishable_key' => [
						'required' => true,
						'type'     => 'string',
					],
					'stripe_user_id'       => [
						'required' => true,
						'type'     => 'string',
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/products', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_products' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'force'     => [
						'required' => false,
						'type'     => 'boolean',
						'default'  => false,
					],
					'test_mode' => [
						'required' => true,
						'type'     => 'boolean',
						'default'  => false,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/prices', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_prices' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'product_id' => [
						'required' => true,
						'type'     => 'string',
					],
					'test_mode'  => [
						'required' => true,
						'type'     => 'boolean',
						'default'  => false,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/switch', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'switch_mode' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'from' => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'test', 'live' ],
					],
					'to'   => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'test', 'live' ],
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/settings', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_settings' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'setting' => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ Settings::ALLOW_CHECKOUT_COUPONS_OPTIONS, Settings::AUTO_DISPLAY_BUY_BUTTON_OPTIONS ],
					],
					'value'   => [
						'required' => true,
						'type'     => 'string',
					],
				],
			],
		] );
	}

	/**
	 * Save the Stripe credentials.
	 *
	 * This method is responsible for saving the Stripe credentials.
	 * It checks the state and if it's valid, it saves the credentials.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 * @throws RandomException If the state is invalid.
	 */
	public function save_credentials( WP_REST_Request $request ) {
		$state      = $request->get_param( 'state' );
		$site_state = Credentials::get_state();
		if ( ! $state || ! $site_state || $state !== $site_state ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => __( 'Invalid state ' . $state . '  #   ' . $site_state, 'thrive-apprentice' ) ], 400 );
		}
		$secret_key           = $request->get_param( 'secret_key' );
		$test_secret_key      = $request->get_param( 'test_secret_key' );
		$publishable_key      = $request->get_param( 'publishable_key' );
		$test_publishable_key = $request->get_param( 'test_publishable_key' );
		$stripe_user_id       = $request->get_param( 'stripe_user_id' );

		Credentials::save_credentials( $secret_key, $publishable_key, $stripe_user_id, $test_secret_key, $test_publishable_key );

		return new WP_REST_Response( [ 'success' => true ] );
	}

	/**
	 * This method is responsible for saving Stripe general settings, those settings are used as default for each product setup .
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_settings( WP_REST_Request $request ) {
		$setting = $request->get_param( 'setting' );
		$value   = $request->get_param( 'value' );

		if ( ! in_array( $setting, [ Settings::ALLOW_CHECKOUT_COUPONS_OPTIONS, Settings::AUTO_DISPLAY_BUY_BUTTON_OPTIONS ] ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => __( 'Invalid setting', 'thrive-apprentice' ) ], 400 );
		}

		Settings::update_setting( $setting, $value );

		return new WP_REST_Response( [ 'success' => true ] );
	}

	/**
	 * This method is responsible for switching the mode of the products(e.g from test to live or vice-versa).
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function switch_mode( WP_REST_Request $request ) {
		$from = $request->get_param( 'from' );
		$to   = $request->get_param( 'to' );
		Settings::switch_products_mode( $from, $to );

		return new WP_REST_Response( [ 'success' => true ] );
	}

	public function disconnect() {
		Credentials::disconnect();

		return new WP_REST_Response( [ 'success' => true ] );
	}

	/**
	 * Get the URL to connect a Stripe account.
	 *
	 * This method is responsible for generating the URL to connect a Stripe account.
	 * It adds the necessary parameters to the API URL.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 * @throws RandomException If the state is invalid.
	 */
	public function get_connect_account_link( WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );
		if ( empty( $url ) ) {
			$url = admin_url( 'admin.php?page=thrive_apprentice#settings/stripe' );
		}

		$url = remove_query_arg( 'tve_stripe_connect_error', $url );

		$data = [
			'customer_site_url'        => $url,
			'endpoint_url'             => $this->get_credentials_endpoint_url(),
			'state'                    => Credentials::get_state(),
			'tve_gateway_connect_init' => 'stripe_connect',
		];

		$response = wp_remote_post( static::API_URL, [
			'body' => $data,
		] );

		$response = wp_remote_retrieve_body( $response );

		return new WP_REST_Response( [ 'success' => filter_var( $response, FILTER_VALIDATE_URL ) !== false, 'url' => $response ] );
	}

	/**
	 * Generic endpoint to listen for Stripe webhooks.
	 *
	 * This method is responsible for handling Stripe webhooks.
	 * It verifies the signature and processes the event.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function generic_listen( WP_REST_Request $request ) {
		$stripe_connection   = Connection_V2::get_instance();
		$webhook_secret      = $stripe_connection->get_webhook_secret();
		$stripe_signature    = $request->get_header( 'stripe-signature' );
		$test_webhook_secret = $stripe_connection->get_test_webhook_secret();
		$success             = false;
		$message             = __( 'Invalid signature', 'thrive-apprentice' );

		if ( empty( $webhook_secret ) ) {
			$webhook_secret = $test_webhook_secret;
		}

		if ( $stripe_signature && $webhook_secret ) {
			try {
				$this->handle_stripe_event( $request, $stripe_signature, $webhook_secret );
				$success = true;
				$message = __( 'Event processed', 'thrive-apprentice' );

			} catch ( Exception $e ) {
				// If the webhook secret doesn't work try with the test secret too
				if ( ! $stripe_connection->get_test_mode() ) {
					try {
						$stripe_connection->set_test_mode( true );
						$this->handle_stripe_event( $request, $stripe_signature, $test_webhook_secret );
						$success = true;
						$message = __( 'Event processed', 'thrive-apprentice' );
					} catch ( Exception $e ) {
						$message = $e->getMessage();
					}
				} else {
					$message = $e->getMessage();
				}
			}
		}

		return new WP_REST_Response( [ 'success' => $success, 'message' => $message ] );
	}

	/**
	 * Handle the Stripe event.
	 *
	 * This method is responsible for handling the Stripe event.
	 * It constructs the event and triggers the necessary actions.
	 *
	 * @param $request          - The request object.
	 * @param $stripe_signature - The Stripe signature.
	 * @param $webhook_secret   - The webhook secret.
	 *
	 * @throws SignatureVerificationException If the signature is invalid.
	 */
	protected function handle_stripe_event( $request, $stripe_signature, $webhook_secret ) {
		$stripe_event = Webhook::constructEvent(
			$request->get_body(),
			$stripe_signature,
			$webhook_secret
		);

		/**
		 * Action triggered when a valid Stripe webhook is received
		 *
		 * @param Event $stripe_event
		 */
		do_action( 'tva_stripe_webhook', $stripe_event );
		do_action( 'tva_stripe_webhook_' . $stripe_event->type, $stripe_event );

		$class_name = Generic::get_class_name( $stripe_event->type );

		if ( class_exists( $class_name ) ) {
			/** @var Generic $event */
			$event = new $class_name( $stripe_event );
			$event->do_action();
		}
	}


	/**
	 * Handle the request to get all products
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_products( WP_REST_Request $request ) {
		$force     = $request->get_param( 'force' );
		$test_mode = $request->get_param( 'test_mode' );
		$message   = '';
		$products  = [];
		$success   = true;

		try {
			$products = Request::get_all_products( $force, $test_mode );
		} catch ( Exception $e ) {
			$message = $e->getMessage();
			$success = false;
		}

		return new WP_REST_Response( [ 'products' => $products, 'success' => $success, 'message' => $message ] );
	}

	/**
	 * Handle the request to get the prices for a product
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_prices( WP_REST_Request $request ) {
		$product_id = $request->get_param( 'product_id' );
		$test_mode  = $request->get_param( 'test_mode' );

		$prices = Request::get_product_prices( $product_id, $test_mode );

		if ( $prices instanceof WP_Error ) {
			return new WP_REST_Response( [ 'success' => false, 'error' => $prices->get_error_message(), ], 400 );
		}

		return new WP_REST_Response( [ 'success' => true, 'prices' => $prices, ] );
	}

	/**
	 * Get the credentials endpoint URL.
	 *
	 * This method is responsible for generating the credentials endpoint URL.
	 *
	 * @return string The credentials endpoint URL.
	 */
	public function get_credentials_endpoint_url() {
		$endpoint = Credentials::get_credentials_endpoint();

		return get_rest_url() . TVA_Stripe_Controller::$namespace . TVA_Stripe_Controller::$version . '/' . $this->base . '/' . $endpoint;
	}

	public function create_page( WP_REST_Request $request ) {
		$title   = $request->get_param( 'title' );
		$page_id = wp_insert_post( [
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => 'page',
		] );
		if ( $page_id ) {
			return new WP_REST_Response( [ 'success' => true, 'url' => get_permalink( $page_id ) ] );
		} else {
			return new WP_REST_Response( [ 'success' => false, 'message' => __( 'Error creating page', 'thrive-apprentice' ) ], 400 );
		}
	}
}
