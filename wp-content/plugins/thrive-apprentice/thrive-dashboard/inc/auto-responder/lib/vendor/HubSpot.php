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
class Thrive_Dash_Api_HubSpot {
	const API_URL = 'https://api.hubapi.com/';

	protected $apiKey;

	/**
	 * Max number of allowed lists to be pulled from '/contacts/v1/lists' endpoint
	 *
	 * @var int
	 */
	protected $_allowed_count = 250;

	/**
	 * @param string $apiKey always required
	 *
	 * @throws Thrive_Dash_Api_HubSpot_Exception
	 */
	public function __construct( $apiKey ) {
		if ( empty( $apiKey ) ) {
			throw new Thrive_Dash_Api_HubSpot_Exception( 'API Key is required' );
		}
		$this->apiKey = $apiKey;
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
			'hapikey' => $this->apiKey,
			'count'   => $this->_allowed_count,
		);

		$cnt  = 0;
		$data = array();

		/**
		 * Do a max of 30 requests getting 250 list items per request with an incremented offset
		 */
		do {

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
	 * @param $webinarKey
	 * @param $name
	 * @param $email
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_HubSpot_Exception
	 */
	public function registerToContactList( $contactListId, $name, $email, $phone ) {
		$params    = array(
			'properties' => array(
				array(
					'property' => 'email',
					'value'    => $email,
				),
				array(
					'property' => 'firstname',
					'value'    => $name ? $name : '',
				),
				array(
					'property' => 'phone',
					'value'    => $phone ? $phone : '',
				),
			),
		);
		$data      = $this->_call( '/contacts/v1/contact/createOrUpdate/email/' . $email . '/?hapikey=' . $this->apiKey, $params, 'POST' );
		$contactId = $data['vid'];

		$request_body = array( 'vids' => array( $contactId ) );
		$this->_call( 'contacts/v1/lists/' . $contactListId . '/add?hapikey=' . $this->apiKey, $request_body, 'POST' );

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
				'Content-type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => $params,
		);

		switch ( $method ) {
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

		if ( $statusMsg != 'OK' ) {
			if ( empty( $statusMsg ) ) {
				$statusMsg = 'Raw response was: ' . $body;
			}
			throw new Thrive_Dash_Api_HubSpot_Exception( 'API call error: ' . $statusMsg );
		}

		return $data;
	}
}