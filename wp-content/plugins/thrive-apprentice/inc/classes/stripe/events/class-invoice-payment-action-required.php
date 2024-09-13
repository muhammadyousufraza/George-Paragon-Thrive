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

class Invoice_Payment_Action_Required extends Invoice_Created {
	protected $type = Event::INVOICE_PAYMENT_ACTION_REQUIRED;
}
