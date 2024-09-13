<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_CampaignMonitor_ClassicEmail {

	/**
	 * @var string
	 */
	protected $_client_id;

	/**
	 * @var Thrive_Dash_Api_CampaignMonitor
	 */
	protected $_manager;

	/**
	 * It's required for this Email to work on with
	 *
	 * @param $client_id string
	 *
	 * @throws Exception
	 */
	public function set_client( $client_id ) {

		if ( empty( $client_id ) ) {
			throw new Exception( 'Invalid client', 400 );
		}

		$this->_client_id = $client_id;
	}

	/**
	 * Required to be able to send requests through API
	 *
	 * @param $manager Thrive_Dash_Api_CampaignMonitor
	 *
	 * @throws Exception
	 */
	public function set_manager( $manager ) {

		if ( false === $manager instanceof Thrive_Dash_Api_CampaignMonitor ) {
			throw new Exception( 'Invalid manager', 400 );
		}

		$this->_manager = $manager;
	}

	/**
	 * Sends the email through API Manager
	 *
	 * @param $message
	 *
	 * @return true
	 * @throws Exception
	 */
	function send( $message ) {

		/**
		 * merge some defaults data
		 */
		$message = array_merge( array(
			'ConsentToTrack' => 'Yes',
		), $message );

		$data = array(
			'body' => json_encode( $message ),
		);

		$response = $this->_manager->request( 'transactional/classicEmail/send?clientId=' . $this->_client_id, 'post', $data );

		if ( false === is_array( $response ) || false === isset( $response[0]['MessageID'] ) ) {
			throw new Exception( 'Mail not sent', 400 );
		}

		return true;
	}
}
