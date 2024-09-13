<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVA_Payment_Gateway_Abstract
 */
abstract class TVA_Payment_Gateway_Abstract {
	/**
	 * Request information
	 *
	 * @var $server
	 */
	protected $server;

	/**
	 * Data from the request
	 *
	 * @var $raw_data
	 */
	protected $raw_data;

	/**
	 * JSON decoded $raw_data
	 *
	 * @var $data
	 */
	protected $data;

	/**
	 * Result Message
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * The status for the message
	 *
	 * @var int
	 */
	protected $status = 200;

	/**
	 * Database object
	 *
	 * @var
	 */
	protected $wpdb;


	/**
	 * The order Object
	 *
	 * @var TVA_Order $order_obj
	 */
	protected $order_obj;

	/**
	 * TVA_Payment_Gateway_Abstract constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;

		$this->init();
		$this->server = $_SERVER;
		$this->read_input();

		TVA_Logger::set_type( 'REQUEST' );
		TVA_Logger::log( 'IPN RECEIVED', (array) $this->data, true, null, 'IPN' );

		$this->log_ipn();

		$verify = $this->verify_request();

		if ( ! $verify ) {
			$this->status  = 400;
			$this->message = 'Request verification failed !';

			return;
		}

		$this->process_notification();
	}

	/**
	 * Anything that needs to be done before starting work with the gateway
	 * should be included in the init method
	 */
	protected function init() {

	}

	protected function log_ipn() {

	}

	/**
	 * Read the data from the input
	 */
	protected function read_input() {
		$this->raw_data = file_get_contents( 'php://input' );
		$this->data     = json_decode( $this->raw_data );
	}

	protected function verify_request() {
		return true;
	}

	/**
	 * Should return a message
	 *
	 * @return bool
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Should return a status code
	 *
	 * @return bool
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Return the response
	 *
	 * @return array
	 */
	public function get_response() {
		return array(
			'code'    => $this->get_status(),
			'message' => $this->get_message(),

		);
	}

	/**
	 * Process the notification
	 */
	protected function process_notification() {
	}
}
