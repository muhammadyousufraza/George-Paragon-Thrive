<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * API wrapper for Citrix (GoToWebinar)
 */
class Thrive_Dash_Api_GoToWebinar {

	/**
	 * API URL
	 */
	const API_URL = 'https://api.getgo.com/';

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @var null|string
	 */
	protected $accessToken;

	/**
	 * @var mixed|string
	 */
	protected $authorization;

	/**
	 * @var null|string
	 */
	protected $organizerKey;

	/**
	 * @var
	 */
	protected $accountKey;

	/**
	 * @var
	 */
	protected $expiresAt;

	/**
	 * @var mixed|string
	 */
	protected $authType;

	/**
	 * @var bool|mixed|string
	 */
	protected $version = false;

	/**
	 * @var bool|mixed|string
	 */
	protected $versioning = false;

	/**
	 * @var
	 */
	protected $username;

	/**
	 * @var
	 */
	protected $password;

	/**
	 * @param string      $apiKey       always required
	 *
	 * @param string|null $accessToken  if the service has been previously connected, this must be passed in
	 * @param string|null $organizerKey if the service has been previously connected, this must be passed in
	 *
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	public function __construct( $apiKey, $accessToken = null, $organizerKey = null, $settings = array() ) {
		if ( empty( $apiKey ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API Key is required' );
		}
		/*
		 * General data required for both API versions
		 */
		$this->apiKey        = $apiKey;
		$this->accessToken   = $accessToken;
		$this->organizerKey  = $organizerKey;
		$this->authorization = ! empty( $settings['auth_key'] ) ? $settings['auth_key'] : '';
		$this->version       = ! empty( $settings['version'] ) ? $settings['version'] : '';
		$this->versioning    = ! empty( $settings['versioning'] ) ? $settings['versioning'] : '';
		$this->expiresAt     = ! empty( $settings['expires_in'] ) ? $settings['expires_in'] : '';
		$this->authType      = ! empty( $settings['auth_type'] ) ? $settings['auth_type'] : '';
		$this->refreshToken  = ! empty( $settings['refresh_token'] ) ? $settings['refresh_token'] : '';
		$this->username      = ! empty( $settings['username'] ) ? $settings['username'] : '';
		$this->password      = ! empty( $settings['password'] ) ? $settings['password'] : '';
	}

	/**
	 * get the required credentials that will need to be stored
	 */
	public function get_credentials() {
		if ( empty( $this->accessToken ) ) {
			return array();
		}

		return array(
			'access_token'  => $this->accessToken,
			'organizer_key' => $this->organizerKey,
			'expires_at'    => $this->expiresAt,
			'version'       => $this->version,
			'versioning'    => $this->versioning,
			'auth_type'     => $this->authType,
			'organizer_key' => $this->organizerKey,
			'expires_in'    => $this->expiresAt,
			'username'      => $this->username,
			'password'      => $this->password,
		);
	}

	/**
	 * @param       $email
	 * @param       $password
	 * @param array $settings
	 *
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	public function directLogin( $email, $password, $settings = array() ) {

		// Used on v2
		if ( isset( $settings['version'] ) && 2 === (int) $settings['version'] ) {
			try {
				$this->_set_auth( $email, $password, $settings );
			} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
				throw new Thrive_Dash_Api_GoToWebinar_Exception( $e->getMessage() );

				return $e->getMessage();
			}

			return;
		}

		$params = array(
			'grant_type' => 'password',
			'user_id'    => $email,
			'password'   => $password,
			'client_id'  => $this->apiKey,
		);

		$data = $this->_call( 'oauth/access_token', $params, 'POST', false, 'url-encoded' );

		$this->accessToken  = $data['access_token'];
		$this->organizerKey = $data['organizer_key'];
		$this->expiresAt    = time() + $data['expires_in'];
		$this->username     = $email;
		$this->password     = $password;

		if ( ! empty( $settings['version'] ) ) {
			$this->version = $settings['version'];
		}

		if ( ! empty( $settings['versioning'] ) ) {
			$this->versioning = $settings['versioning'];
		}
	}

	/**
	 * get the list of webinars scheduled for the future for the current organizer (specified by organizer_key
	 */
	public function getUpcomingWebinars() {
		return $this->_call( 'G2W/rest/organizers/' . $this->organizerKey . '/upcomingWebinars' );
	}

	/**
	 * register a new user to a webinar
	 *
	 * @param $webinarKey
	 * @param $firstName
	 * @param $lastName
	 * @param $email
	 * @param $phone
	 */
	public function registerToWebinar( $webinarKey, $firstName, $lastName, $email, $phone ) {
		$params = array(
			'firstName' => $firstName,
			'lastName'  => $lastName,
			'email'     => $email,
		);

		if ( isset( $phone ) ) {
			$params['phone'] = $phone;
		}

		$uri = 'G2W/rest/organizers/' . $this->organizerKey . '/webinars/' . $webinarKey . '/registrants?oauth_token=' . $this->accessToken;

		$this->_call( $uri, $params, 'POST', false );

		return true;
	}

	/**
	 * @param       $email
	 * @param       $password
	 * @param array $settings
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	protected function _set_auth( $email, $password, $settings = array() ) {
		$params = array(
			'grant_type'    => 'password',
			'username'      => $email,
			'password'      => $password,
			'Authorization' => $this->authorization,
		);

		try {
			$data = $this->_call( 'oauth/v2/token', $params, 'POST', false, 'url-encoded' );
		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			$this->_error = $e->getMessage();
			throw new Thrive_Dash_Api_GoToWebinar_Exception( $e->getMessage() );
		}

		$this->accessToken  = $data['access_token'];
		$this->organizerKey = $data['organizer_key'];
		$this->refreshToken = $data['refresh_token'];
		$this->expiresAt    = time() + $data['expires_in'];
		$this->authType     = $data['token_type'];
		$this->username     = $email;
		$this->password     = $password;

		if ( ! empty( $settings['version'] ) ) {
			$this->version = $settings['version'];
		}

		if ( ! empty( $settings['versioning'] ) ) {
			$this->versioning = $settings['versioning'];
		}
	}

	/**
	 * @param        $path
	 * @param array  $params
	 * @param string $method
	 * @param bool   $auth
	 * @param string $content_type
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	protected function _call( $path, $params = array(), $method = 'GET', $auth = true, $content_type = 'application/json' ) {
		if ( $auth ) {
			$params['oauth_token'] = $this->accessToken;
		}

		$url = self::API_URL . ltrim( $path, '/' );

		$args = array(
			'headers' => array(
				'Accept' => 'application/json',
			),
		);

		if ( $content_type == 'application/json' ) {
			$args['headers']['Content-type'] = $content_type;
		}

		// For API v2 used on first instance
		if ( ! empty( $params['Authorization'] ) ) {
			$args['headers']['Authorization'] = "Basic {$params['Authorization']}";
			unset( $params['Authorization'] );
		}

		switch ( $method ) {
			case 'POST':
				$args['body'] = $content_type == 'application/json' ? json_encode( $params ) : $params; // default to www-url-encoded
				$result       = tve_dash_api_remote_post( $url, $args );
				break;
			case 'GET':
			default:
				$query_string = '';
				foreach ( $params as $k => $v ) {
					$query_string .= $query_string ? '&' : '';
					$query_string .= $k . '=' . $v;
				}
				if ( $query_string ) {
					$url .= ( strpos( $url, '?' ) !== false ? '&' : '?' ) . $query_string;
				}
				$result = tve_dash_api_remote_get( $url, $args );
				break;
		}

		if ( $result instanceof WP_Error ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'Failed connecting to GoToWebinar: ' . $result->get_error_message() );
		}

		$body = trim( wp_remote_retrieve_body( $result ) );

		$data = @json_decode( $body, true, 512, JSON_BIGINT_AS_STRING );
		if ( empty( $data ) ) {
			/**
			 * try also without the JSON_BIGINT_AS_STRING
			 */
			$data = json_decode( $body, true );
		}

		if ( ! empty( $data['int_err_code'] ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data['int_err_code'] );
		}

		if ( ! empty( $data['errorCode'] ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data['errorCode'] . ( ! empty( $data['description'] ) ? "Error description: " . $data["description"] : '' ) );
		}

		if ( ! empty( $data['err'] ) ) {
			if ( ! empty( $data['message'] ) ) {
				throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data["message"] );
			} else {
				throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . var_export( $data, true ) );
			}
		}

		/**
		 * SUP-1111 GoToWebinar cannot connect
		 */
		if ( ! empty( $data['error'] ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call returned error: ' . $data['error'] );
		}

		return $data;
	}

}