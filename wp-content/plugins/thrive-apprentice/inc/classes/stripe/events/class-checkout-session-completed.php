<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Events;

use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use TVA\Stripe\Orders\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Checkout_Session_Completed extends Generic {

	protected $type = Event::CHECKOUT_SESSION_COMPLETED;

	protected $data;

	protected $line_items = [];


	public function build_data() {
		parent::build_data();
		$this->fetch_line_items();
		$this->data->line_items = $this->line_items;

		$order = new Checkout( $this->data, $this->order_status );
		$order->save();
	}

	private function fetch_line_items( $starting_after = '' ) {
		$filter = [ 'limit' => 100 ];
		if ( $starting_after !== '' ) {
			$filter['starting_after'] = $starting_after;
		}

		try {
			$response = $this->stripe_connection->checkout->sessions->allLineItems(
				$this->data->id,
				$filter
			);

			$this->line_items = array_merge( $this->line_items, $response->data );

			if ( $response->has_more ) {
				$this->fetch_line_items( $response->data[ count( $response->data ) - 1 ]->id );
			}
		} catch ( ApiErrorException $e ) {

		}
	}
}
