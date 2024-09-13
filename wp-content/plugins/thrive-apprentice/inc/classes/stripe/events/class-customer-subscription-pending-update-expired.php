<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Events;

use Stripe\Event;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Customer_Subscription_Pending_Update_Expired extends Customer_Subscription_Created {

	protected $type = Event::CUSTOMER_SUBSCRIPTION_PENDING_UPDATE_EXPIRED;

}
