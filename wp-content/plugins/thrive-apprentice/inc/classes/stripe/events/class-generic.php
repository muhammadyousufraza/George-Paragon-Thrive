<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Events;

use Stripe\Event;
use Stripe\StripeClient;
use TVA\Stripe\Connection_V2;
use TVA_Const;
use function do_action;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Generic {
	protected $type;

	/**
	 * @var Event $original_event
	 */
	protected $original_event;

	protected $data;

	protected $stripe_connection;

	protected $order_status = TVA_Const::STATUS_COMPLETED;


	public function __construct( Event $event ) {
		$this->original_event    = $event;
		$this->stripe_connection = Connection_V2::get_instance()->get_client();
		$this->build_data();
	}

	public function build_data() {
		$this->data = $this->original_event->data->object;
	}

	public function do_action() {
		do_action( 'tva_stripe_event_' . $this->get_type(), $this->get_data() );
	}

	/**
	 * @return mixed
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function get_original_event() {
		return $this->original_event;
	}

	/**
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * @return StripeClient
	 */
	public function get_stripe_connection() {
		return $this->stripe_connection;
	}

	/**
	 * Generate the class name for the event type
	 *
	 *
	 * @param $event_type
	 *
	 * @return string
	 */
	public static function get_class_name( $event_type ) {
		$delimiters = [ '_', '.' ];
		$event_type = str_replace( $delimiters, ' ', $event_type );
		$event_type = ucwords( $event_type );
		$event_type = str_replace( ' ', '_', $event_type );

		return "TVA\\Stripe\\Events\\{$event_type}";
	}
}
