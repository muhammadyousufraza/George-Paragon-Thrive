<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Events;

use Stripe\Event;
use TVA\Stripe\Orders\Generic as Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Customer_Subscription_Resumed extends Customer_Subscription_Created {

	protected $type = Event::CUSTOMER_SUBSCRIPTION_RESUMED;

	protected $order_status = Order::RESUMED_STATUS;

}
