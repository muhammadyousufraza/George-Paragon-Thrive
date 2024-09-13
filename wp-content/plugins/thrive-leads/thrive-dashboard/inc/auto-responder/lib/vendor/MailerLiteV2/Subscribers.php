<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLiteV2_Subscribers extends Thrive_Dash_Api_MailerLiteV2_ApiAbstract {

	protected $endpoint = 'subscribers';

	/**
	 * Search for a subscriber by email or custom field value
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function search( $query ) {
		$this->endpoint .= '/search';

		return $this->rest_client->get( $this->endpoint, array( 'query' => $query ) );
	}

}
