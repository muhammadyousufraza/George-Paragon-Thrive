<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLite_Groups extends Thrive_Dash_Api_MailerLite_ApiAbstract {

	protected $endpoint = 'groups';

	/**
	 * Get subscribers from group
	 *
	 * @param int    $group_id
	 * @param string $type
	 * @param array  $params
	 *
	 * @return [type]
	 */
	public function getSubscribers( $group_id, $type = null, $params = array() ) {
		$endpoint = $this->endpoint . '/' . $group_id . '/subscribers';

		if ( $type !== null ) {
			$endpoint .= '/' . $type;
		}

		$response = $this->rest_client->get( $endpoint, $params );

		return $response['body'];
	}

	/**
	 * Add single subscriber to group
	 *
	 * @param int   $group_id
	 * @param array $subscriber_data
	 * @param array $params
	 *
	 * @return [type]
	 */
	public function add_subscriber( $group_id, $subscriber_data = array(), $params = array() ) {
		$endpoint = $this->endpoint . '/' . $group_id . '/subscribers';

		return $this->rest_client->post( $endpoint, $subscriber_data );
	}

	/**
	 * Remove subscriber from group
	 *
	 * @param int $group_id
	 * @param int $subscriber_id
	 *
	 * @return [type]
	 */
	public function removeSubscriber( $group_id, $subscriber_id ) {
		$endpoint = $this->endpoint . '/' . $group_id . '/subscribers/' . $subscriber_id;

		$response = $this->rest_client->delete( $endpoint );

		return $response['body'];
	}

	/**
	 * Batch add subscribers to group
	 *
	 * @param int   $group_id
	 * @param array $subscribers
	 *
	 * @return [type]
	 */
	public function importSubscribers( $group_id, $subscribers ) {
		$endpoint = $this->endpoint . '/' . $group_id . '/subscribers/import';

		$response = $this->rest_client->post( $endpoint, array( 'subscribers' => $subscribers ) );

		return $response['body'];
	}
}
