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

class Invoice_Finalization_Failed extends Invoice_Created {
	protected $type = Event::INVOICE_FINALIZATION_FAILED;
}
