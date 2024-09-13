<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_Dash_Api_SendOwl
 */
class Thrive_Dash_Api_SendOwl {

	/**
	 * the query string
	 */
	const QUERY_STRING = 'api/v1/';

	/**
	 * @var $apiKey
	 */
	protected $apiKey;

	/**
	 * @var
	 */
	protected $secretKey;

	/**
	 * @var string
	 */
	protected $domain = 'www.sendowl.com/';

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * Setup the Http Client
	 *
	 * Thrive_Dash_Api_SendOwl_Exception constructor.
	 *
	 * @param $options
	 */
	public function __construct( $options ) {
		$this->secretKey = $options['secretKey'];
		$this->apiKey    = $options['apiKey'];
		$this->baseUrl   = 'https://' . $this->apiKey . ':' . $this->secretKey . '@' . $this->domain . self::QUERY_STRING;
	}

	/**
	 * Build the baseUrl
	 *
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->domain . '/' . self::QUERY_STRING;
	}

	/**
	 * Get product list
	 *
	 * @return array
	 */
	public function getProducts( $params = array() ) {

		return $this->call( array( 'endpoint' => 'products', 'body' => $params ), 'GET' );
	}

	/**
	 * Prepare the call CRUD data
	 *
	 * @param array  $params which are sent in body
	 * @param string $method
	 *
	 * @return array
	 */
	public function call( $params = array(), $method = 'POST' ) {
		$response = ( isset( $params['body'] ) && ! empty( $params['body'] ) ) ? $this->send( $method, $this->baseUrl . $params['endpoint'], $params['body'] ) : $this->send( $method, $this->baseUrl . $params['endpoint'] );

		return $response;
	}

	/**
	 * @param       $method
	 * @param       $endpointUrl
	 * @param null  $body
	 * @param array $headers
	 *
	 * @return mixed
	 */
	public function send( $method, $endpointUrl, $body = null, array $headers = array() ) {
		$headers = array(
			'Accept' => 'application/json',
		);

		switch ( $method ) {
			case 'GET':
				$fn = 'tve_dash_api_remote_get';
				break;
			default:
				$endpointUrl = $endpointUrl . '&' . $body;
				$fn          = 'tve_dash_api_remote_post';
				break;
		}

		$response = $fn( $endpointUrl, array(
			'body'      => $body,
			'timeout'   => 15,
			'headers'   => $headers,
			'sslverify' => false,
		) );

		return $this->handleResponse( $response );
	}

	/**
	 * @param $response
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_SendOwl_Exception
	 */
	protected function handleResponse( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_SendOwl_Exception( 'Failed connecting: ' . $response->get_error_message() );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					$result = json_decode( $response['body'], true );

					return $result;
					break;
				case 400:
					throw new Thrive_Dash_Api_SendOwl_Exception( 'Missing a required parameter or calling invalid method' );
					break;
				case 401:
					throw new Thrive_Dash_Api_SendOwl_Exception( 'Invalid API key provided!' );
					break;
				case 403:
					$body = json_decode( $response['body'], true );
					throw new Thrive_Dash_Api_SendOwl_Exception( $body['error'] );
					break;
				case 404:
					throw new Thrive_Dash_Api_SendOwl_Exception( 'Can\'t find requested items' );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Returns all licenses associated with a order
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	public function getOrderLicenses( $order_id ) {
		$oreder_uri = $this->prepareRequestParams( $order_id, 'orders', array( 'licenses' ) );

		return $this->call( array( 'endpoint' => $oreder_uri ), 'GET' );
	}

	/**
	 * Prepare uri for request
	 *
	 * @param null  $search_by
	 * @param       $endpoint
	 * @param array $actions
	 * @param array $params
	 *
	 * @return string
	 */
	private function prepareRequestParams( $search_by, $endpoint, $actions = array(), $params = array() ) {
		$return = $endpoint;

		if ( $search_by ) {
			$return .= '/' . $search_by;
		}

		if ( ! empty( $actions ) && is_array( $actions ) ) {
			$return_params = implode( '/', $actions );
			$return        .= '/' . $return_params;
		}

		if ( ! empty( $params ) && is_array( $params ) ) {
			$return .= '?';
			$i      = 0;
			foreach ( $params as $param => $value ) {
				if ( $i > 0 ) {
					$return .= '&';
				}
				$return .= $param . '=' . $value;
				$i ++;
			}
		}

		return $return;
	}

	/**
	 * Returns all possible discount codes
	 *
	 * @return array
	 */
	public function getDiscounts( $params = array() ) {
		return $this->call( array( 'endpoint' => 'discounts', 'body' => $params ), 'GET' );
	}

	/**
	 * Returns a specific discount code
	 *
	 * @param $discount_id
	 *
	 * @return array
	 */
	public function getDiscountById( $discount_id ) {
		$discount_uri = $this->prepareRequestParams( $discount_id, 'discounts' );

		return $this->call( array( 'endpoint' => $discount_uri ), 'GET' );
	}

	/**
	 * Searches for a specific discount by discount code.
	 *
	 * @param $term
	 *
	 * @return array
	 */
	public function getDiscountByCode( $term ) {
		$discount_uri = $this->prepareRequestParams( null, 'discounts', array( 'search' ), array( 'term' => $term ) );

		return $this->call( array( 'endpoint' => $discount_uri ), 'GET' );
	}

	/**
	 * Updates a discount code
	 *
	 * @param $discount_id
	 *
	 * @return array
	 */
	public function updateDiscountById( $discount_id ) {
		$discount_uri = $this->prepareRequestParams( $discount_id, 'discounts' );

		return $this->call( array( 'endpoint' => $discount_uri ), 'POST' );
	}

	/**
	 * Deletes a discount code
	 *
	 * @param $discount_id
	 *
	 * @return array
	 */
	public function deleteDiscountById( $discount_id ) {
		$discount_uri = $this->prepareRequestParams( $discount_id, 'discounts' );

		return $this->call( array( 'endpoint' => $discount_uri ), 'DELETE' );
	}

	/**
	 * Deletes a single code while retaining the discount code
	 *
	 * @param $discount_id
	 * @param $discount_code
	 *
	 * @return array
	 */
	public function deleteDiscountRetainCode( $discount_id, $discount_code ) {
		$discount_uri = $this->prepareRequestParams( $discount_id, 'discounts', array( 'codes', $discount_code ) );

		return $this->call( array( 'endpoint' => $discount_uri ), 'DELETE' );
	}

	/**
	 * Returns all licenses associated with a product
	 *
	 * @param $product_id
	 *
	 * @return array
	 */
	public function getProductLicenses( $product_id ) {
		$product_uri = $this->prepareRequestParams( $product_id, 'products', array( 'licenses' ) );

		return $this->call( array( 'endpoint' => $product_uri ), 'GET' );
	}

	/**
	 * Returns all licenses matching the passed key for a given product, or an empty collection if no matches
	 *
	 * @param $product_id
	 * @param $license
	 *
	 * @return array
	 */
	public function checkLicenseValidity( $product_id, $license ) {
		$product_uri = $this->prepareRequestParams( $product_id, 'products', array(
			'licenses',
			'check_valid'
		), array( 'key' => $license ) );

		return $this->call( array( 'endpoint' => $product_uri ), 'GET' );
	}

	/**
	 * Creates licenses from the passed in keys, associated with a product
	 *
	 * @param $product_id
	 *
	 * @return array
	 */
	public function setProductLicenses( $product_id ) {
		$product_uri = $this->prepareRequestParams( $product_id, 'products', array( 'licenses' ) );

		return $this->call( array( 'endpoint' => $product_uri ), 'POST' );
	}

	/**
	 * Returns all the orders
	 *
	 * @return array
	 */
	public function getOrders() {
		return $this->call( array( 'endpoint' => 'orders' ), 'GET' );
	}

	/**
	 * Searches for a specific order by buyer email, buyer name, giftee email, giftee name, business name or transaction id.
	 *
	 * @param $term
	 *
	 * @return array
	 */
	public function getOrderByTerm( $term ) {
		$oreder_uri = $this->prepareRequestParams( null, 'orders', array( 'search' ), array( 'term' => $term ) );

		return $this->call( array( 'endpoint' => $oreder_uri ), 'GET' );
	}

	/**
	 * Returns a specific order by it's id
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function getOrderById( $id ) {
		$oreder_uri = $this->prepareRequestParams( $id, 'orders' );

		return $this->call( array( 'endpoint' => $oreder_uri ), 'GET' );
	}

	/**
	 * @return array
	 */
	public function getBundles( $params = array() ) {
		return $this->call( array( 'endpoint' => 'packages', 'body' => $params ), 'GET' );
	}

	/**
	 * Searches for a specific bundle by name.
	 *
	 * @param $term
	 *
	 * @return array
	 */
	public function getBundleByTerm( $term ) {
		$bundle_uri = $this->prepareRequestParams( null, 'packages', array( 'search' ), array( 'term' => $term ) );

		return $this->call( array( 'endpoint' => $bundle_uri ), 'GET' );
	}

	/**
	 * Returns a specific bundle
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function getBundleById( $id ) {
		$bundle_uri = $this->prepareRequestParams( $id, 'packages' );

		return $this->call( array( 'endpoint' => $bundle_uri ), 'GET' );
	}

	/**
	 * Returns all possible subscriptions
	 *
	 * Subscriptions are only supported on the premium plan and higher.
	 *
	 * @return array
	 */
	public function getSubscriptions() {
		return $this->call( array( 'endpoint' => 'subscriptions' ), 'GET' );
	}

	/**
	 * Returns a specific subscription
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function getSubscriptionById( $id ) {
		$subscriptions_uri = $this->prepareRequestParams( $id, 'subscriptions' );

		return $this->call( array( 'endpoint' => $subscriptions_uri ), 'GET' );
	}

	/**
	 * Searches for a specific subscription by name. This is useful when you know the name but not the subscription ID.
	 *
	 * @param $term
	 *
	 * @return array
	 */
	public function getSubscriptionByTerm( $term ) {
		$subscriptions_uri = $this->prepareRequestParams( null, 'subscriptions', array( 'search' ), array( 'term' => $term ) );

		return $this->call( array( 'endpoint' => $subscriptions_uri ), 'GET' );
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function getProductById( $id ) {
		$product_uri = $this->prepareRequestParams( $id, 'products' );

		return $this->call( array( 'endpoint' => $product_uri ), 'GET' );
	}

	/**
	 * Delete product by id
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function deleteProductById( $id ) {
		$product_uri = $this->prepareRequestParams( $id, 'products' );

		return $this->call( array( 'endpoint' => $product_uri ), 'DELETE' );
	}
}
