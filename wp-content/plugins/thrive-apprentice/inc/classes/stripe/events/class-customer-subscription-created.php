<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Events;

use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use TVA\Stripe\Orders\Subscription;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Customer_Subscription_Created extends Generic {
	protected $type = Event::CUSTOMER_SUBSCRIPTION_CREATED;

	protected $subscription_items = [];

	public function build_data() {
		parent::build_data();
		$this->fetch_line_items();
		$this->data->subscription_items = $this->subscription_items;

		$order = new Subscription( $this->data, $this->order_status );
		$order->save();
	}


	private function fetch_line_items( $starting_after = '' ) {
		$filter = [
			'limit'        => 100,
			'subscription' => $this->data->id,
		];
		if ( $starting_after !== '' ) {
			$filter['starting_after'] = $starting_after;
		}

		try {
			$response = $this->stripe_connection->subscriptionItems->all(
				$filter
			);

			$this->subscription_items = array_merge( $this->subscription_items, $response->data );

			if ( $response->has_more ) {
				$this->fetch_line_items( $response->data[ count( $response->data ) - 1 ]->id );
			}
		} catch ( ApiErrorException $e ) {

		}
	}
}
