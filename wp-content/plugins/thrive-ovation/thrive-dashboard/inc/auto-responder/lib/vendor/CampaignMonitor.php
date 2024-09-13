<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_CampaignMonitor {

	/**
	 * @var string
	 */
	protected $api_key;

	/**
	 * @var string
	 */
	protected $url = 'https://api.createsend.com/api/v3.2';

	public function __construct( $api_key ) {

		$this->api_key = $api_key;
	}

	/**
	 * Returns a list of clients set for current CM Account
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_clients() {

		$clients  = array();
		$response = $this->request( '/clients', 'get' );

		foreach ( $response as $item ) {
			$clients[] = array(
				'id'   => $item['ClientID'],
				'name' => $item['Name'],
			);
		}

		return $clients;
	}

	/**
	 * Based on client id returns its lists to work with
	 *
	 * @param $client_id
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_client_lists( $client_id ) {

		$lists = array();

		$response = $this->request( '/clients/' . $client_id . '/lists', 'get' );

		foreach ( $response as $item ) {
			$lists[] = array(
				'id'   => $item['ListID'],
				'name' => $item['Name'],
			);
		}

		return $lists;
	}

	/**
	 * @param $list_id
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_list_custom_fields( $list_id ) {

		return $this->request( '/lists/' . $list_id . '/customfields', 'get' );
	}

	/**
	 * Instantiate a Thrive_Dash_Api_CampaignMonitor_List and returns it
	 *
	 * @param $list_id
	 *
	 * @return Thrive_Dash_Api_CampaignMonitor_List
	 * @throws Exception
	 */
	public function get_list( $list_id ) {

		$list = new Thrive_Dash_Api_CampaignMonitor_List( $list_id );
		$list->set_manager( $this );

		return $list;
	}

	/**
	 * Makes a REST call to CM API
	 *
	 * @param string $route
	 * @param string $method
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public function request( $route, $method = 'post', $args = array() ) {

		$url = trailingslashit( trim( $this->url, '/' ) ) . trim( $route, '/' ) . '.json';

		if ( strpos( $url, 'transactional' ) !== false ) {
			$url = trim( $url, '.json' );
		}

		$defaults = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $this->api_key . ':' ),
			),
		);

		$args = array_merge( $defaults, $args );

		switch ( strtolower( $method ) ) {
			case 'post':
				$fn = 'tve_dash_api_remote_post';
				break;
			case 'delete':
				$url = $url. '?' . http_build_query($args['body']);
				$args['method'] = 'DELETE';
				$fn = 'tve_dash_api_remote_request';
				break;
			default:
				$fn = 'tve_dash_api_remote_get';
				break;
		}

		$response = $fn( $url, $args );

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code > 210 ) {
			$code    = isset( $body['Code'] ) ? $body['Code'] : 401;
			$message = isset( $body['Message'] ) ? $body['Message'] : 'Bad Request';
			throw new Exception( $message, $code );
		}

		return $body;
	}

	/**
	 * Instantiate a Thrive_Dash_Api_CampaignMonitor_ClassicEmail email
	 *
	 * @return Thrive_Dash_Api_CampaignMonitor_ClassicEmail
	 * @throws Exception
	 */
	public function transactional() {

		$email = new Thrive_Dash_Api_CampaignMonitor_ClassicEmail();
		$email->set_manager( $this );

		return $email;
	}
}
