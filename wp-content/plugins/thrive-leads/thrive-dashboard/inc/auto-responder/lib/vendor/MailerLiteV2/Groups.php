<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLiteV2_Groups extends Thrive_Dash_Api_MailerLiteV2_ApiAbstract {

	protected $endpoint = 'groups';

	/**
	 * Add single subscriber to group
	 *
	 * @param $group_id
	 * @param $subscriber_data
	 * @param $params
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function add_subscriber( $group_id, $subscriber_data = array(), $params = array() ) {
		$endpoint       = 'subscribers';
		$subscriberData = array_merge( $subscriber_data, array( 'groups' => [ $group_id ] ) );

		return $this->rest_client->post( $endpoint, $subscriberData );
	}
}

