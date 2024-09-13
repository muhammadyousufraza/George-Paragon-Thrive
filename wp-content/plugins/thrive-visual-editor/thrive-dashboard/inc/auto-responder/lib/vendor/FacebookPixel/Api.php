<?php


namespace TVD\Autoresponder\FacebookPixel;

class Api {

	/**
	 * @var string
	 */
	const VERSION = ApiConfig::APIVersion;

	/**
	 * @var Api
	 */
	protected static $instance;

	/**
	 * @var SessionInterface
	 */
	private $session;

	/**
	 * @var Client
	 */
	protected $httpClient;

	/**
	 * @var string
	 */
	protected $defaultGraphVersion;

	/**
	 * @param Client           $http_client
	 * @param SessionInterface $session A Facebook API session
	 */
	public function __construct( Client $http_client, SessionInterface $session ) {
		$this->httpClient = $http_client;
		$this->session    = $session;
	}

	/**
	 * @param string $app_id
	 * @param string $app_secret
	 * @param string $access_token
	 *
	 * @return static
	 */
	public static function init( $app_id, $app_secret, $access_token, $log_crash = true ) {
		$session = new Session( $app_id, $app_secret, $access_token );
		$api     = new static( new Client(), $session );
		static::setInstance( $api );

		return $api;
	}

	/**
	 * @return Api|null
	 */
	public static function instance() {
		return static::$instance;
	}

	/**
	 * @param Api $instance
	 */
	public static function setInstance( Api $instance ) {
		static::$instance = $instance;
	}

	/**
	 * @param SessionInterface $session
	 *
	 * @return Api
	 */
	public function getCopyWithSession( SessionInterface $session ) {
		$api = new self( $this->getHttpClient(), $session );
		$api->setDefaultGraphVersion( $this->getDefaultGraphVersion() );

		return $api;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function base64UrlEncode( $string ) {
		$str = strtr( base64_encode( $string ), '+/', '-_' );

		return str_replace( '=', '', $str );
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @param array  $params
	 *
	 * @return RequestInterface
	 */
	public function prepareRequest(
		$path,
		$method = RequestInterface::METHOD_GET,
		Headers $headers = null,
		array $params = array()
	) {

		$request = $this->getHttpClient()->createRequest();
		$request->setMethod( $method );
		$request->setGraphVersion( $this->getDefaultGraphVersion() );
		$request->setPath( $path );
		$request->setHeaders( $headers );

		if ( $method === RequestInterface::METHOD_GET ) {
			$params_ref = $request->getQueryParams();
		} else {
			$params_ref = $request->getBodyParams();
		}

		if ( ! empty( $params ) ) {
			$params_ref->enhance( $params );
		}

		$params_ref->enhance( $this->getSession()->getRequestParameters() );

		return $request;
	}

	/**
	 * @param RequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function executeRequest( RequestInterface $request ) {
		return $request->execute();
	}

	/**
	 * @return string
	 */
	public function getDefaultGraphVersion() {
		if ( $this->defaultGraphVersion === null ) {
			$match = array();
			if ( preg_match( "/^\d+\.\d+/", static::VERSION, $match ) ) {
				$this->defaultGraphVersion = $match[0];
			}
		}

		return $this->defaultGraphVersion;
	}

	/**
	 * @param string $version
	 */
	public function setDefaultGraphVersion( $version ) {
		$this->defaultGraphVersion = $version;
	}

	/**
	 * Make graph api calls
	 *
	 * @param string $path   Ads API endpoint
	 * @param string $method Ads API request type
	 * @param array  $params Assoc of request parameters
	 *
	 * @return ResponseInterface Graph API responses
	 */
	public function call(
		$path,
		$method = RequestInterface::METHOD_GET,
		Headers $headers = null,
		array $params = array(),
		array $file_params = array()
	) {

		$request = $this->prepareRequest( $path, $method, $headers, $params );

		if ( ! empty( $file_params ) ) {
			foreach ( $file_params as $key => $value ) {
				$request->getFileParams()->offsetSet( $key, $value );
			}
		}

		return $this->executeRequest( $request );
	}

	/**
	 * @return SessionInterface
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * @return Client
	 */
	public function getHttpClient() {
		return $this->httpClient;
	}
}
