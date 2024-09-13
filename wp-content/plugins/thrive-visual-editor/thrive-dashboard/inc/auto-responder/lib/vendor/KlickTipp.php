<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_KlickTipp {
	private $sessionId;
	private $sessionName;
	private $error;
	private $user;
	private $url;
	private $password;


	/**
	 * Thrive_Dash_Api_KlickTipp constructor.
	 */
	public function __construct( $username, $password, $service = 'https://api.klick-tipp.com' ) {
		$this->user     = $username;
		$this->password = $password;
		$this->url      = $service;
	}


	/**
	 * Save tags that don't exist and return a full id list
	 *
	 * @param $list string
	 *
	 * @return array
	 */
	public function tagsIdList( $list ) {

		$tags_id_list = array();

		if ( ! empty( $list ) ) {

			$tags_arr      = explode( ',', $list );
			$existing_tags = $this->getTags();

			foreach ( $tags_arr as $new_tag ) {

				$new_tag = trim( $new_tag );

				if ( empty( $new_tag ) ) {
					continue;
				}

				if ( ! in_array( $new_tag, array_values( $existing_tags ) ) ) {

					try {
						$new_tag_id = (int) $this->createTag( $new_tag );
						array_push( $tags_id_list, $new_tag_id );
						continue;

					} catch ( Thrive_Dash_Api_KlickTipp_Exception $e ) {
						$this->error = $e->getMessage();
					}
				}

				$existing_id = array_search( $new_tag, $existing_tags );
				array_push( $tags_id_list, $existing_id );
			}
		}

		return $tags_id_list;
	}

	/**
	 * @param $new_tag
	 *
	 * @return mixed Tag id
	 * @throws Thrive_Dash_Api_KlickTipp_Exception
	 */
	public function createTag( $new_tag ) {

		if ( ! $new_tag ) {
			throw new Thrive_Dash_Api_KlickTipp_Exception( 'Could not create empty tag ' . $new_tag );
		}

		$response = $this->_http_request( '/tag', 'POST', array( 'name' => $new_tag ) );

		if ( empty( $response->data ) ) {
			throw new Thrive_Dash_Api_KlickTipp_Exception( 'API error: [empty response data]' );
		}

		if ( strpos( $response->data, 'A tag or SmartLink with the name' ) !== false ) {
			throw new Thrive_Dash_Api_KlickTipp_Exception( 'API error: ' . $response->data );
		}

		return $response->data;
	}

	public function getTags() {
		try {
			$this->login();
		} catch ( Thrive_Dash_Api_KlickTipp_Exception $e ) {
			echo( 'Could not connect to Klick Tipp using the provided data (' . $e->getMessage() . ')' );
		}

		$response = $this->_http_request( '/tag' );

		return ! empty( $response->data ) ? $response->data : array();
	}

	/**
	 * Get all subscription processes (lists) of the logged in user. Requires to be logged in.
	 *
	 * @return array A associative array <list id> => <list name>
	 * @throws Thrive_Dash_Api_KlickTipp_Exception
	 *
	 */
	public function getLists() {
		try {
			$this->login();
		} catch ( Thrive_Dash_Api_KlickTipp_Exception $e ) {
			echo( 'Could not connect to Klick Tipp using the provided data (' . $e->getMessage() . ')' );
		}

		$response = $this->_http_request( '/list' );

		if ( empty( $response->error ) && is_array( $response->data ) ) {
			return $response->data;
		} else {
			$message = 'Could not retrieve lists: ' . $response->data;
			throw new Thrive_Dash_Api_KlickTipp_Exception( $message );
		}
	}

	/**
	 * Subscribe an email. Requires to be logged in.
	 *
	 * @param mixed $email  The email address of the subscriber.
	 * @param mixed $listid (optional) The id subscription process.
	 * @param mixed $tagid  (optional) The id of the manual tag the subscriber will be tagged with.
	 * @param mixed $fields (optional) Additional fields of the subscriber.
	 *
	 * @return mixed An object representing the Klicktipp subscriber object.
	 * @throws Thrive_Dash_Api_KlickTipp_Exception Exception
	 *
	 */
	public function subscribe( $email, $listid = 0, $tagid = 0, $fields = array() ) {
		if ( empty( $email ) ) {
			throw new Thrive_Dash_Api_KlickTipp_Exception( 'Illegal Arguments' );
		}

		// subscribe
		$data = array(
			'email' => $email,
		);
		if ( ! empty( $listid ) ) {
			$data['listid'] = $listid;
		}
		if ( ! empty( $tagid ) ) {
			$data['tagid'] = $tagid;
		}
		if ( ! empty( $fields ) ) {
			$data['fields'] = $fields;
		}
		$response = $this->_http_request( '/subscriber', 'POST', $data );

		if ( empty( $response->error ) ) {
			return ! isset( $response->data ) ? null : $response->data;
		} else {
			$message = 'Subscription failed: ' . $response->error;
			throw new Thrive_Dash_Api_KlickTipp_Exception( $message );
		}
	}

	/**
	 * Login
	 *
	 * @param mixed $username The login name of the user to login.
	 * @param mixed $password The password of the user.
	 *
	 * @return TRUE on success
	 * @throws Thrive_Dash_Api_KlickTipp_Exception Exception
	 *
	 */
	public function login() {
		if ( empty( $this->user ) || empty( $this->password ) ) {
			throw new Thrive_Dash_Api_KlickTipp_Exception( 'Illegal Arguments' );
		}

		// Login
		$data     = array(
			'username' => $this->user,
			'password' => $this->password,
		);
		$response = $this->_http_request( '/account/login', 'POST', $data, false );

		if ( empty( $response->error ) && isset( $response->data ) && isset( $response->data->sessid ) ) {
			$this->sessionId   = $response->data->sessid;
			$this->sessionName = $response->data->session_name;

			return true;
		} else {
			$message = 'Login failed: ' . ( empty( $response->data ) ? '' : $response->data );
			throw new Thrive_Dash_Api_KlickTipp_Exception( $message );
		}
	}

	/**
	 * Logs out the user currently logged in.
	 *
	 * @return TRUE on success
	 * @throws Thrive_Dash_Api_KlickTipp_Exception Exception
	 *
	 */
	public function logout() {
		$response = $this->_http_request( '/account/logout', 'POST' );

		if ( empty( $response->error ) ) {
			return true;
		} else {
			$message = 'Logout failed: ' . $response->error;
			throw new Thrive_Dash_Api_KlickTipp_Exception( $message );
		}
	}


	/**
	 * Helper function.
	 * Establishes the system connection to the website.
	 *
	 * @param           $path
	 * @param string    $method
	 * @param null      $data
	 * @param bool|TRUE $usesession
	 *
	 * @return stdClass
	 */
	private function _http_request( $path, $method = 'GET', $data = null, $usesession = true ) {

		$args = array();

		// Set session cookie if applicable
		if ( $usesession && ! empty( $this->sessionName ) ) {
			$args['cookies'] = array(
				new WP_Http_Cookie( array(
					'name'  => $this->sessionName,
					'value' => $this->sessionId,
				) ),
			);
		}

		//get serialized response
		$args['headers'] = array( "Accept" => "application/vnd.php.serialized" );

		$url = $this->url . $path;

		//build parameters depending on the send method type
		if ( $method == 'GET' ) {
			$url     .= '?' . build_query( $data );
			$request = tve_dash_api_remote_get( $url, $args );
		} elseif ( $method == 'POST' ) {
			$args['body'] = $data;
			$request      = tve_dash_api_remote_post( $url, $args );
		} else {
			$request = null;
		}

		$result = new stdClass();

		if ( is_wp_error( $request ) ) {
			$result->error = $request->get_error_message();
		}

		$result->data = maybe_unserialize( wp_remote_retrieve_body( $request ) );

		return $result;
	}

	/**
	 * Get subscription process (list) redirection url for given subscription.
	 *
	 * @param mixed $listid The id of the subscription process.
	 * @param mixed $email  The email address of the subscriber.
	 *
	 * @return A redirection url as defined in the subscription process.
	 */
	public function subscription_process_redirect( $listid, $email ) {
		if ( empty( $listid ) || empty( $email ) ) {
			$this->error = 'Illegal Arguments';

			return false;
		}

		// update
		$data     = array(
			'listid' => $listid,
			'email'  => $email,
		);
		$response = $this->_http_request( '/list/redirect', 'POST', $data );

		if ( empty( $response->error ) ) {
			return ! isset( $response->data ) ? '' : $response->data;
		} else {
			$this->error = 'Subscription process get redirection url failed: ' . $response->error;

			return false;
		}
	}

	/**
	 * @param       $email
	 * @param array $tags
	 *
	 * @return bool
	 */
	public function tagByEmail( $email, $tags = array() ) {

		if ( empty( $tags ) ) {
			return false;
		}

		if ( ! is_array( $tags ) ) {
			$tags = [ $tags ];
		}

		$data = array(
			'email'  => $email,
			'tagids' => $tags,
		);

		$response = $this->_http_request( '/subscriber/tag', 'POST', $data );
		if ( isset( $response->data ) && $response->data ) {
			return true;
		}

		return false;
	}
}
