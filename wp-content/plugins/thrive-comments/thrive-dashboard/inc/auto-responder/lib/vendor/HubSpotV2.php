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
 * API wrapper for HubSpot
 */
class Thrive_Dash_Api_HubSpotV2 {
	const API_URL = 'https://api.hubapi.com/';

	protected $access_token;

	/**
	 * Max number of allowed lists to be pulled from '/contacts/v1/lists' endpoint
	 *
	 * @var int
	 */
	protected $_allowed_count = 250;

	/**
	 * @param string $access_token always required
	 *
	 * @throws Thrive_Dash_Api_HubSpot_Exception
	 */
	public function __construct( $access_token ) {
		if ( empty( $access_token ) ) {
			throw new Thrive_Dash_Api_HubSpot_Exception( 'Access token is required' );
		}
		$this->access_token = $access_token;
	}

	/**
	 * get the static contact lists
	 * HubSpot is letting us to work only with static contact lists
	 * "Please note that you cannot manually add (via this API call) contacts to dynamic lists - they can only be updated by the contacts app."
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_HubSpot_Exception
	 */
	public function getContactLists() {
		$params = array(
			'count' => $this->_allowed_count,
		);

		$cnt  = 0;
		$data = array();

		/**
		 * Do a max of 30 requests getting 250 list items per request with an incremented offset
		 */
		do {
			/* TODO This should be changed when HubSpot releases the new endpoints for lists: https://developers.hubspot.com/docs/api/marketing/contact-lists  */
			$result = $this->_call( '/contacts/v1/lists/static', $params, 'GET' );

			if ( is_array( $result ) && ! empty( $result['lists'] ) ) {
				$data = array_merge( $data, (array) $result['lists'] );
			}

			// Offset set
			if ( ! empty( $result['offset'] ) ) {
				$params['offset'] = $result['offset'];
			}

			$has_more = isset( $result['has-more'] ) ? $result['has-more'] : false;
			$cnt ++;

			// Never trust APIs :) [ Enough requests here: 250 x 30 = 7.500 items in list ]
			if ( $cnt > 30 ) {
				$has_more = false;
			}
		} while ( true === $has_more );

		return is_array( $data ) ? $data : array();
	}

	/**
	 * register a new user to a static contact list
	 *
	 * @param $contactListId
	 * @param $name
	 * @param $email
	 * @param $phone
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_HubSpot_Exception
	 */
	public function registerToContactList( $contactListId, $name, $email, $phone ) {
		$path   = '/crm/v3/objects/contacts/';
		$method = 'POST';
		try {
			/* Firstly we need to see that the user is not already added */
			$user = $this->_call( '/contacts/v1/contact/email/' . $email . '/profile' );
		} catch ( Thrive_Dash_Api_HubSpot_Exception $e ) {
			$user = null;
		}

		$params = array(
			'properties' => array(
				'email'     => $email,
				'firstname' => $name ? $name : '',
				'phone'     => $phone ? $phone : '',
			),
		);
		/* If we have an id, it meens the user is already added and we need to update it */
		if ( ! empty( $user['vid'] ) ) {
			$path   .= $user['vid'];
			$method = 'PATCH';

			/* Do not update properties if they are empty */
			if ( empty( $name ) ) {
				unset( $params['properties']['firstname'] );
			}
			if ( empty( $phone ) ) {
				unset( $params['properties']['phone'] );
			}
		}

		/* Call for add or update  contact contact */
		$data = $this->_call( $path, $params, $method );

		$request_body = array( 'vids' => array( $data['id'] ) );

		/* TODO This should be changed when HubSpot releases the new endpoints for lists: https://developers.hubspot.com/docs/api/marketing/contact-lists  */
		$this->_call( 'contacts/v1/lists/' . $contactListId . '/add', $request_body, 'POST' );

		return true;
	}


	/**
	 * perform a webservice call
	 *
	 * @param string $path   api path
	 * @param array  $params request parameters
	 * @param string $method GET or POST
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_HubSpot_Exception
	 */
	protected function _call( $path, $params = array(), $method = 'GET' ) {
		$url = self::API_URL . ltrim( $path, '/' );

		$args = array(
			'headers' => array(
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $this->access_token,
			),
			'body'    => $params,
		);

		switch ( $method ) {
			case 'PATCH':
				$http           = _wp_http_get_object();
				$args['method'] = $method;
				$args['body']   = json_encode( $params );
				$result         = $http->request( $url, $args );
				break;
			case 'POST':
				$args['body'] = json_encode( $params );
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
			throw new Thrive_Dash_Api_HubSpot_Exception( 'Failed connecting to HubSpot: ' . $result->get_error_message() );
		}

		$body      = trim( wp_remote_retrieve_body( $result ) );
		$statusMsg = trim( wp_remote_retrieve_response_message( $result ) );
		$data      = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			throw new Thrive_Dash_Api_HubSpot_Exception( 'API call error. Response was: ' . $body );
		}

		if ( $statusMsg !== 'OK' && $statusMsg !== 'Created' ) {
			if ( empty( $statusMsg ) ) {
				$statusMsg = 'Raw response was: ' . $body;
			}
			throw new Thrive_Dash_Api_HubSpot_Exception( 'API call error: ' . $statusMsg );
		}

		return $data;
	}
}