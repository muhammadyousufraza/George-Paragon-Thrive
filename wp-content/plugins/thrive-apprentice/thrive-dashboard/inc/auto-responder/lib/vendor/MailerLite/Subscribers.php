<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLite_Subscribers extends Thrive_Dash_Api_MailerLite_ApiAbstract {

	protected $endpoint = 'subscribers';

	/**
	 * Get groups subscriber belongs to
	 *
	 * @param  int $subscriber_id
	 * @param  array $params
	 *
	 * @return [type]
	 */
	public function getGroups( $subscriber_id, $params = array() ) {
		$this->endpoint .= $subscriber_id . '/groups';

		$response = $this->rest_client->get( $this->endpoint, $params );

		return $response['body'];
	}

	/**
	 * Get activity of subscriber
	 *
	 * @param  int $subscriber_id
	 * @param  string $type
	 * @param  array $params
	 *
	 * @return [type]
	 */
	public function getActivity( $subscriber_id, $type = null, $params = array() ) {
		$this->endpoint .= $subscriber_id . '/activity';

		if ( $type !== null ) {
			$this->endpoint .= '/' . $type;
		}

		$response = $this->rest_client->get( $this->endpoint, $params );

		return $response['body'];
	}

	/**
	 * Seach for a subscriber by email or custom field value
	 *
	 * @param  string $query
	 *
	 * @return [type]
	 */
	public function search( $query ) {
		$this->endpoint .= '/search';

		return $this->rest_client->get( $this->endpoint, array( 'query' => $query ) );
	}

}