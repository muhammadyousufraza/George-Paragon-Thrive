<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Events;

use Stripe\Event;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Customer_Subscription_Paused extends Customer_Subscription_Created {

	protected $type = Event::CUSTOMER_SUBSCRIPTION_PAUSED;

	protected $order_status = TVA_Const::STATUS_FAILED;
}
