<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_GoToWebinar {

	/**
	 * API URL
	 */
	private $_apiUrl = 'https://api.getgo.com/';

	/**
	 * API version
	 *
	 * @var string
	 */
	protected $_v = 'v2';

	/**
	 * @var string
	 */
	protected $authorization;

	/**
	 * @var mixed|string
	 */
	protected $authType = 'Bearer';

	/**
	 * @var null|string
	 */
	protected $accessToken;

	/**
	 * @var null|string
	 */
	protected $organizerKey;

	/**
	 * @var
	 */
	protected $refreshToken;

	/**
	 * @var
	 */
	protected $accountKey;

	/**
	 * @var
	 */
	protected $expiresAt;

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
	 * @var int
	 */
	protected $renewed = 0;

	/**
	 * Maximum size allowed by the API
	 *
	 * @var int
	 */
	protected $page_size = 200;


	/**
	 * Thrive_Dash_Api_GoToWebinar constructor.
	 *
	 * @param       $auth_key
	 * @param null  $accessToken
	 * @param null  $organizerKey
	 * @param array $settings
	 *
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	public function __construct( $auth_key, $accessToken = null, $organizerKey = null, $settings = array() ) {
		if ( empty( $auth_key ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'Authorization Key is required' );
		}

		$settings['auth_key']     = $auth_key;
		$settings['accessToken']  = $accessToken;
		$settings['organizerKey'] = $organizerKey;

		$this->_setInstanceData( $settings );

	}

	/**
	 * @param $settings
	 */
	protected function _setInstanceData( $settings ) {

		$this->authorization = ! empty( $settings['auth_key'] ) ? $settings['auth_key'] : '';
		$this->accessToken   = ! empty( $settings['accessToken'] ) ? $settings['accessToken'] : '';
		$this->organizerKey  = ! empty( $settings['organizerKey'] ) ? $settings['organizerKey'] : '';
		$this->version       = ! empty( $settings['version'] ) ? $settings['version'] : '';
		$this->versioning    = ! empty( $settings['versioning'] ) ? $settings['versioning'] : '';
		$this->expiresAt     = ! empty( $settings['expires_in'] ) ? $settings['expires_in'] : '';
		$this->authType      = ! empty( $settings['auth_type'] ) ? $settings['auth_type'] : '';
		$this->refreshToken  = ! empty( $settings['refresh_token'] ) ? $settings['refresh_token'] : '';
		$this->username      = ! empty( $settings['username'] ) ? $settings['username'] : '';
		$this->password      = ! empty( $settings['password'] ) ? $settings['password'] : '';
	}

	/**
	 * Get the required credentials that will need to be stored
	 *
	 * @return array
	 */
	public function get_credentials() {

		if ( empty( $this->accessToken ) ) {
			return array();
		}

		return array(
			'access_token'  => $this->accessToken,
			'auth_type'     => $this->authType,
			'organizer_key' => $this->organizerKey,
			'expires_in'    => $this->expiresAt,
			'version'       => $this->version,
			'versioning'    => $this->versioning,
			'username'      => $this->username,
			'password'      => $this->password,
		);
	}

	/**
	 * @param       $data
	 * @param array $settings
	 *
	 * @return bool
	 */
	protected function _setData( $data, $settings = array() ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		$this->accessToken  = $data['access_token'];
		$this->organizerKey = $data['organizer_key'];
		$this->refreshToken = $data['refresh_token'];
		$this->expiresAt    = time() + $data['expires_in'];
		$this->authType     = $data['token_type'];

		if ( ! empty( $settings['version'] ) ) {
			$this->version = $settings['version'];
		}

		if ( ! empty( $settings['versioning'] ) ) {
			$this->versioning = $settings['versioning'];
		}

		if ( ! empty( $settings['username'] ) ) {
			$this->username = $settings['username'];
		}

		if ( ! empty( $settings['password'] ) ) {
			$this->username = $settings['password'];
		}

		return true;
	}

	/**
	 * Save the new data into DB
	 */
	protected function save() {

		$connection = Thrive_Dash_List_Manager::connection_instance( 'gotowebinar' );
		$connection->set_credentials( $this->get_credentials() );
		$connection->save();
	}

	/**
	 * Retreive and set authentication data
	 *
	 * @param       $email
	 * @param       $password
	 * @param array $settings
	 * @param bool  $save
	 *
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	protected function _set_auth( $email, $password, $settings = array(), $save = false ) {

		$params               = array(
			'grant_type' => 'password',
			'username'   => $email,
			'password'   => $password,
			'headers'    => array(
				'auth_type'     => 'Basic',
				'Authorization' => $this->authorization,
			),
		);
		$settings['username'] = $email;
		$settings['password'] = $password;

		try {

			$data = $this->_call( sprintf( 'oauth/%s/token', $this->_v ), $params, 'POST', false );
			$this->_setData( $data, $settings );

		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( $e->getMessage() );
		}

		$this->username = $email;
		$this->password = $password;
		$this->renewed  = 1;

		if ( $save ) {
			$this->save();
		}
	}

	/**
	 * Direct login + setters
	 *
	 * @param       $email
	 * @param       $password
	 * @param array $settings
	 *
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	public function directLogin( $email, $password, $settings = array() ) {
		try {
			$this->_set_auth( $email, $password, $settings );
		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( $e->getMessage() );
		}
	}

	/**
	 * Get upcoming webinars
	 *
	 * @return bool|array
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	public function getUpcomingWebinars() {

		$webinars    = array();
		$page_size   = $this->page_size;
		$total_items = 0;
		$page_nr     = 0;

		do {
			// ISO8601 dates not accepted by the API, so:
			$params = array(
				'fromTime' => date( 'Y-m-d' ) . 'T' . date( 'H:i:s' ) . 'Z',
				'toTime'   => date( 'Y-m-d', strtotime( '+1 year' ) ) . 'T' . date( 'H:i:s' ) . 'Z',
				'page'     => $page_nr,
				'size'     => $page_size,
			);

			$response = $this->_call( sprintf( 'G2W/rest/%s/organizers/%s/webinars', $this->_v, $this->organizerKey ), $params, 'GET', false );

			if ( empty( $response['_embedded'] ) || empty( $response['_embedded']['webinars'] ) ) {
				break;
			}
			if ( ! empty( $response['page'] ) && (int) $response['page']['totalElements'] > 0 ) {
				$total_items = $response['page']['totalElements'];


				foreach ( $response['_embedded']['webinars'] as $webinar ) {
					$webinars[ $webinar['webinarKey'] ]['webinarKey']      = $webinar['webinarKey'];
					$webinars[ $webinar['webinarKey'] ]['subject']         = $webinar['subject'] . ' (' . date( 'Y-m-d H:i:s', strtotime( $webinar['times'][0]['startTime'] ) ) . ')';
					$webinars[ $webinar['webinarKey'] ]['registrationUrl'] = $webinar['registrationUrl'];
				}

				$page_nr ++;
			}
		} while ( count( $webinars ) < $total_items );

		return $webinars;
	}

	/**
	 * Register a new user to a webinar
	 *
	 * @param $webinarKey
	 * @param $firstName
	 * @param $lastName
	 * @param $email
	 * @param $phone
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	public function registerToWebinar( $webinarKey, $firstName, $lastName, $email, $phone ) {

		$this->_renewTokens();

		$params = array(
			'firstName' => $firstName,
			'lastName'  => $lastName,
			'email'     => $email,
			'headers'   => array(
				'Accept' => 'application/json',
			),
		);

		if ( isset( $phone ) ) {
			$params['phone'] = $phone;
		}

		$url = sprintf( 'G2W/rest/%s/organizers/%s/webinars/%s/registrants?resendConfirmation=FALSE', $this->_v, $this->organizerKey, $webinarKey );

		$this->_call( $url, $params, 'POST', false );

		return true;
	}

	/**
	 * Get new auth tokens and save them
	 */
	protected function _renewTokens() {

		$settings = array(
			'version'    => $this->version,
			'versioning' => $this->versioning,
		);

		// Grab saved data if not already in instance
		if ( ! $this->username || ! $this->password ) {
			$connection  = Thrive_Dash_List_Manager::connection_instance( 'gotowebinar' );
			$credentials = $connection->get_credentials();

			$settings       = array_merge( $settings, $credentials );
			$this->username = $credentials['username'];
			$this->password = $credentials['password'];
		}

		$this->_set_auth( $this->username, $this->password, $settings, true );
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
	protected function _call( $path, $params = array(), $method = 'GET', $auth = true, $content_type = 'application/x-www-form-urlencoded' ) {

		$json_encode = false;
		$url         = $this->_apiUrl . $path;

		$args = array(
			'headers' => array(
				'Authorization' => "{$this->authType} " . $this->accessToken,
				'Content-type'  => $content_type,
			),
		);

		if ( ! empty( $params['headers']['Accept'] ) ) {
			$json_encode               = 'application/json' == $params['headers']['Accept'];
			$args['headers']['Accept'] = $params['headers']['Accept'];
		}

		if ( ! empty( $params['headers']['Authorization'] ) && ! empty( $params['headers']['auth_type'] ) ) {
			$args['headers']['Authorization'] = $params['headers']['auth_type'] . ' ' . $params['headers']['Authorization'];
		}

		switch ( $method ) {
			case 'POST':

				$args['body'] = $content_type == 'application/json' || $json_encode ? json_encode( $params ) : $params;
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
			if ( $data['int_err_code'] === 'InvalidToken' ) {
				$this->_renewTokens();
			}
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data['int_err_code'] );
		}

		if ( ! empty( $data['errorCode'] ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data['errorCode'] . ( ! empty( $data['description'] ) ? 'Error description: ' . $data['description'] : '' ) );
		}

		if ( ! empty( $data['err'] ) ) {
			if ( ! empty( $data['message'] ) ) {
				throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data['message'] );
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

		if ( ! empty( $data['error_description'] ) ) {
			throw new Thrive_Dash_Api_GoToWebinar_Exception( 'API call error: ' . $data['error_description'] );
		}

		return $data;
	}
}

