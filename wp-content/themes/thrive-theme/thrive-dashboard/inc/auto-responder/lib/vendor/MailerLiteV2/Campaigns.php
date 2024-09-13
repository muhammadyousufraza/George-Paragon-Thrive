<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLiteV2_Campaigns extends Thrive_Dash_Api_MailerLiteV2_ApiAbstract {

	protected $endpoint = 'campaigns';

	/**
	 * Add custom html to campaign
	 *
	 * @param $campaign_id
	 * @param $content_data
	 * @param $params
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function addContent( $campaign_id, $content_data = array(), $params = array() ) {
		$endpoint = $this->endpoint . '/' . $campaign_id . '/content';

		$response = $this->rest_client->put( $endpoint, $content_data );

		return $response['body'];
	}

	/**
	 * Trigger action: send
	 *
	 * @param $campaign_id
	 * @param $settings_data
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function send( $campaign_id, $settings_data ) {
		$endpoint = $this->endpoint . '/' . $campaign_id . '/send';

		$response = $this->rest_client->post( $endpoint, $settings_data );

		return $response['body'];
	}

	/**
	 * Trigger action: cancel
	 *
	 * @param $campaign_id
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function cancel( $campaign_id ) {
		$endpoint = $this->endpoint . '/' . $campaign_id . '/cancel';

		$response = $this->rest_client->post( $endpoint );

		return $response['body'];
	}
}
