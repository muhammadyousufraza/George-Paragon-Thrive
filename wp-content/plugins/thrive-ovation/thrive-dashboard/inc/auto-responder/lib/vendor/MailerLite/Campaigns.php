<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLite_Campaigns extends Thrive_Dash_Api_MailerLite_ApiAbstract {

	protected $endpoint = 'campaigns';

	/**
	 * Add custom html to campaign
	 *
	 * @param int $campaign_id
	 * @param array $content_data
	 * @param array $params
	 *
	 * @return [type]
	 */
	public function addContent( $campaign_id, $content_data = array(), $params = array() ) {
		$endpoint = $this->endpoint . '/' . $campaign_id . '/content';

		$response = $this->rest_client->put( $endpoint, $content_data );

		return $response['body'];
	}

	/**
	 * Trigger action: send
	 *
	 * @param  int $campaign_id
	 * @param  array $settings_data
	 *
	 * @return [type]
	 */
	public function send( $campaign_id, $settings_data ) {
		$endpoint = $this->endpoint . '/' . $campaign_id . '/actions/send';

		$response = $this->rest_client->post( $endpoint, $settings_data );

		return $response['body'];
	}

	/**
	 * Trigger action: cancel
	 *
	 * @param  int $campaignId
	 *
	 * @return [type]
	 */
	public function cancel( $campaign_id ) {
		$endpoint = $this->endpoint . '/' . $campaign_id . '/actions/cancel';

		$response = $this->rest_client->post( $endpoint );

		return $response['body'];
	}
}