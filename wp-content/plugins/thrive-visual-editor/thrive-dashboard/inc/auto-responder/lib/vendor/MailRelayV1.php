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
 * Class Thrive_Dash_Api_MailRelayV1
 * Wrapper for another version MailRelay's API
 * - {user}.ipzmarketing.com/api/v1
 */
class Thrive_Dash_Api_MailRelayV1 {

	/**
	 * @var string
	 */
	protected $_base_url;

	/**
	 * @var string
	 */
	protected $_api_key;

	/**
	 * @var string
	 */
	protected $uri = '/api/v1';

	/**
	 * Thrive_Dash_Api_MailRelayV1 constructor.
	 *
	 * @param string $base_url
	 * @param string $api_key
	 */
	public function __construct( $base_url, $api_key ) {

		$this->_base_url = untrailingslashit( $base_url );
		$this->_api_key  = $api_key;
	}

	/**
	 * Makes a request to API
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $data
	 * @param array  $headers
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	protected function _request( $route, $method = 'get', $data = array(), $headers = array() ) {

		$data = array_filter( $data );

		$method = strtoupper( $method );
		$body   = json_encode( $data );
		$route  = '/' . trim( $route, '/' );
		$url    = untrailingslashit( $this->_base_url . $this->uri ) . untrailingslashit( $route );

		switch ( $method ) {
			case 'GET':
				$fn   = 'tve_dash_api_remote_get';
				$url  = add_query_arg( $data, $url );
				$body = '';
				break;
			default:
				$fn = 'tve_dash_api_remote_post';
				break;
		}

		$args = array(
			'body'      => $body,
			'timeout'   => 15,
			'headers'   => array_merge( array(
				'Content-Type' => 'application/json',
				'X-AUTH-TOKEN' => $this->_api_key,
			), $headers ),
			'method'    => $method,
			'sslverify' => false,
		);

		$response = $fn( $url, $args );

		return $this->handle_response( $response );
	}

	/**
	 * Get lists/groups from MailRelay
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_list() {

		$lists = $this->_request( '/groups', 'get',
			array(
				'page'     => 0,
				'per_page' => 1000,
			)
		);

		return $lists;
	}
	/**
	 * Adds a subscriber to a group into MailRelay
	 * - does a check for existence before sending it through API
	 *
	 * @param $list_id
	 * @param $args
	 *
	 * @return array with the new or updated subscriber
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function add_subscriber( $list_id, $args ) {

		$list_id = (int) $list_id;

		if ( ! $list_id ) {
			throw new Thrive_Dash_Api_MailRelay_Exception( 'Invalid list id', 400 );
		}

		if ( false === is_array( $args ) || false === isset( $args['email'] ) ) {
			throw new  Thrive_Dash_Api_MailRelay_Exception( 'Invalid email', 400 );
		}

		/**
		 * if subscriber has phone custom field set then send it along with it
		 */
		if ( isset( $args['customFields'] ) && is_array( $args['customFields'] ) && isset( $args['customFields']['f_phone'] ) ) {

			$phone = $args['customFields']['f_phone'];
			unset( $args['customFields'] );

			try {
				$phone_field = $this->get_custom_field( 'thrive_phone' );

				if ( empty( $phone_field ) ) {//if the phone custom field does not exists then create it
					$phone_field = $this->create_custom_field(
						array(
							'label'      => 'Phone',
							'tag_name'   => 'thrive_phone',
							'field_type' => 'text',
						)
					);
				}

				if ( is_array( $phone_field ) && ! empty( $phone_field['id'] ) ) {
					$args['custom_fields'][ $phone_field['id'] ] = $phone;
				}
			} catch ( Exception $e ) {
			}
		}

		$args = array_merge(
			array(
				'group_ids' => array( $list_id ),
				'status'    => 'active',
			),
			$args
		);

		return $this->_request( '/subscribers/sync', 'post', $args );
	}

	/**
	 * Makes an API requests for all custom_fields and loops through them
	 * if there exists any with a $tag_name then returns it
	 *
	 * @param string $tag_name
	 *
	 * @return null|array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_custom_field( $tag_name ) {

		$tag_name     = (string) $tag_name;
		$custom_field = null;
		$fields       = $this->_request( '/custom_fields' );

		foreach ( $fields as $field ) {
			if ( $field['tag_name'] === $tag_name ) {
				$custom_field = $field;
				break;
			}
		}

		return $custom_field;
	}

	/**
	 * Makes a POST request to API with a custom_field data
	 *
	 * @param array $field
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function create_custom_field( $field ) {

		if ( false === is_array( $field ) ) {
			$field = array();
		}

		$field = array_merge( array(
			'required' => false,
		), $field );

		$field = $this->_request( '/custom_fields', 'post', $field );

		return $field;
	}

	/**
	 * Processes the response got from API
	 *
	 * @param WP_Error|array $response
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	protected function handle_response( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_MailRelay_Exception( sprintf( __( 'Failed connecting: %s', 'thrive-dash' ), $response->get_error_message() ) );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					$result = json_decode( $response['body'], true );

					return $result;
					break;
				case 400:
					throw new Thrive_Dash_Api_MailRelay_Exception( __( 'Missing a required parameter or calling invalid method', 'thrive-dash' ) );
					break;
				case 401:
					throw new Thrive_Dash_Api_MailRelay_Exception( __( 'Invalid API key provided!', 'thrive-dash' ) );
					break;
				case 404:
					throw new Thrive_Dash_Api_MailRelay_Exception( __( "Can't find requested items", 'thrive-dash' ) );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function sendEmail( $args ) {

		$senders = $this->get_senders();

		if ( ! is_array( $senders ) || empty( $senders ) ) {
			throw new Thrive_Dash_Api_MailRelay_Exception( __( 'No senders available', 'thrive-dash' ), 400 );
		}

		$email_args = array(
			'from'      => array(
				'name'  => $senders[0]['name'],
				'email' => $senders[0]['email'],
			),
			'to'        => array(
				array(
					'name'  => $args['emails'][0]['name'],
					'email' => $args['emails'][0]['email'],
				),
			),
			'subject'   => $args['subject'],
			'html_part' => $args['html'],
			'smtp_tags' => array( 'string' ),
		);

		return $this->_request( '/send_emails', 'post', $email_args );
	}

	/**
	 * Get a list of senders
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_senders() {

		return $this->_request( '/senders' );
	}
}
