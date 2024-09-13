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
 * Class Thrive_Dash_Api_MailRelay
 */
class Thrive_Dash_Api_MailRelay {

	/**
	 * the query string which is appended to user's domain
	 * and results an url where the API calls are made
	 */
	const QUERY_STRING = 'ccm/admin/api/version/2/&type=json';

	/**
	 * @var string $api_key
	 */
	protected $api_key;

	/**
	 * @var string $domain
	 */
	protected $domain;

	/**
	 * @var string $base_url
	 */
	protected $base_url;

	/**
	 * @var int $group_id
	 */
	protected $group_id;

	/**
	 * Thrive_Dash_Api_MailRelay constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options ) {

		$this->api_key = $options['apiKey'];
		$this->domain  = untrailingslashit( $options['host'] );
	}

	/**
	 * Get all groups
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_list() {

		$response = $this->call( array( 'function' => 'getGroups' ), 'GET' );

		if ( $response['status'] != 1 ) {
			$body = $response['error'];

			throw new Thrive_Dash_Api_MailRelay_Exception( ucwords( $body ) );
		}

		return $response['data'];
	}

	/**
	 * Get a subscriber by email
	 *
	 * @param string $email
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_subscriber( $email ) {

		$args = array(
			'function' => 'getSubscribers',
			'email'    => $email,
		);

		return $this->call( $args, 'GET' );
	}

	/**
	 * Add a subscriber
	 *
	 * @param int   $group_id
	 * @param array $args
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function add_subscriber( $group_id, $args ) {

		$this->group_id = $group_id;
		/**
		 * check if email already exists so we can update it
		 */
		$response = $this->get_subscriber( $args['email'] );

		$subscriber = $response['data'];

		if ( ! empty( $subscriber ) ) {
			return $this->update_subscriber( $subscriber, $args );
		}

		$args['function'] = 'add_subscriber';
		$args['groups'][] = $group_id;

		return $this->call( $args, 'POST' );
	}

	/**
	 * Update a subscriber
	 *
	 * @param $subscriber
	 * @param $args
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function update_subscriber( $subscriber, $args ) {

		$args['function'] = 'updateSubscriber';
		$args['id']       = $subscriber[0]['id'];
		$args['groups']   = $subscriber[0]['groups'];
		if ( is_array( $subscriber[0]['groups'] ) && ! in_array( $this->group_id, $subscriber[0]['groups'] ) ) {
			$args['groups'][] = $this->group_id;
		}

		return $this->call( $args, 'POST' );
	}

	/**
	 * Send an email
	 *
	 * @param $args
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function sendEmail( $args ) {

		$args['function']        = 'sendMail';
		$args['mailboxFromId']   = 1;
		$args['mailboxReplyId']  = 1;
		$args['mailboxReportId'] = 1;

		$mailboxes = $this->get_mail_boxes();

		if ( $mailboxes['status'] == 1 ) {
			$args['mailboxFromId']   = $mailboxes['data'][0]['id'];
			$args['mailboxReplyId']  = $mailboxes['data'][0]['id'];
			$args['mailboxReportId'] = $mailboxes['data'][0]['id'];
		}

		$packages = $this->get_packages();

		$args['packageId'] = $packages['status'] == 1 ? $packages['data'][0]['id'] : 6;

		if ( empty( $args['emails'] ) ) {
			throw new Thrive_Dash_Api_MailRelay_Exception( 'Nor recepients found' );
		}
		$result = $this->call( $args, 'POST' );

		if ( $result['status'] == 0 ) {
			throw new Thrive_Dash_Api_MailRelay_Exception( $result['error'] );
		}

		return $result;
	}

	/**
	 * Get Mailboxes
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_mail_boxes() {

		return $this->call( array( 'function' => 'getMailboxes' ), 'GET' );
	}

	/**
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function get_packages() {

		return $this->call( array( 'function' => 'getPackages' ), 'GET' );
	}

	/**
	 * Prepare the call CRUD data
	 *
	 * @param array  $params
	 * @param string $method
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	public function call( $params = array(), $method = 'POST' ) {

		$params['apiKey'] = $this->api_key;
		$this->base_url   = $this->get_base_url();

		switch ( $method ) {
			case 'GET':
				$response = $this->send( 'GET', $this->base_url . '&' . http_build_query( $params ) );
				break;
			default:
				$response = $this->send( 'POST', $this->base_url, $params );
				break;
		}

		return $response;
	}

	/**
	 * Build the base_url
	 *
	 * @return string
	 */
	public function get_base_url() {

		return $this->domain . '/' . self::QUERY_STRING;
	}

	/**
	 * Execute HTTP request
	 *
	 * @param       $method
	 * @param       $endpoint_url
	 * @param null  $body
	 * @param array $headers
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	protected function send( $method, $endpoint_url, $body = null, array $headers = array() ) {

		switch ( $method ) {
			case 'GET':
				$fn = 'tve_dash_api_remote_get';
				break;
			default:
				$fn = 'tve_dash_api_remote_post';
				break;
		}

		$response = $fn( $endpoint_url, array(
			'body'      => $body,
			'timeout'   => 15,
			'headers'   => $headers,
			'sslverify' => false,
		) );

		return $this->handle_response( $response );
	}

	/**
	 * Process the response we're getting
	 *
	 * @param WP_Error|array $response
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailRelay_Exception
	 */
	protected function handle_response( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_MailRelay_Exception( 'Failed connecting: ' . $response->get_error_message() );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					$result = json_decode( $response['body'], true );

					return $result;
					break;
				case 400:
					throw new Thrive_Dash_Api_MailRelay_Exception( 'Missing a required parameter or calling invalid method' );
					break;
				case 401:
					throw new Thrive_Dash_Api_MailRelay_Exception( 'Invalid API key provided!' );
					break;
				case 404:
					throw new Thrive_Dash_Api_MailRelay_Exception( "Can't find requested items" );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}
}
