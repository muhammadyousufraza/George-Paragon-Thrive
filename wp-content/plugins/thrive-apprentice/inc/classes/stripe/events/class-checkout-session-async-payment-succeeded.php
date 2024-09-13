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

class Checkout_Session_Async_Payment_Succeeded extends Checkout_Session_Completed {
	protected $type = Event::CHECKOUT_SESSION_ASYNC_PAYMENT_SUCCEEDED;
}
