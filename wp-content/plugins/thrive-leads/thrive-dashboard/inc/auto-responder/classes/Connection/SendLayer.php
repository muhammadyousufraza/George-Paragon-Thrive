<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_SendLayer extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'email';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'SendLayer';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sendlayer' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {
		$ajax_call = defined( 'DOING_AJAX' ) && DOING_AJAX;

		$key    = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';

		if ( empty( $key ) ) {
			return $ajax_call ? __( 'You must provide a valid SendLayer key', 'thrive-dash' ) : $this->error( __( 'You must provide a valid SendLayer key', 'thrive-dash' ) );
		}

		$this->set_credentials( compact( 'key' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $ajax_call ? sprintf( __( 'Could not connect to SendLayer using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) : $this->error( sprintf( __( 'Could not connect to SendLayer using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		$this->success( __( 'SendLayer connected successfully', 'thrive-dash' ) );

		if ( $ajax_call ) {
			return true;
		}
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_SendLayer $sendlayer */
		$sendlayer = $this->get_api();

		$from_email = get_option( 'admin_email' );
		$to         = array( array( 'email' => $from_email ) );
		$name = $from_email;

		if ( is_user_logged_in() ) { 
			$user =  wp_get_current_user();
			$name = $user->display_name;
		}

		$subject      = 'API connection test';
		$text_content = $html_content = 'This is a test email from Thrive Leads SendLayer API.';
		
		try {
			$sendlayer->sendMessage([
				'name'		=> $name,
				'from'      => $from_email,
				'to'        => $to,
				'subject'   => $subject,
				'text'      => $text_content,
				'html'      => $html_content,
				'multipart' => true,
			]);
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'sendlayer' );
		}

		return true;
	}

	/**
	 * Send the same email to multiple addresses
	 *
	 * @param $data
	 *
	 * @return bool|string
	 */
	public function sendMultipleEmails( $data ) {
		$sendlayer = $this->get_api();

		$emails = !empty($data['emails']) ? $data['emails'] : array();
		$formatted_emails  = array();
		foreach($emails as $email) {
			array_push($formatted_emails, array('email' => $email));
		}

		$from_email = get_option( 'admin_email' );
		try {
			$messsage = array(
				'name'		 => empty ( $data['from_name'] ) ? '' : $data['from_name'],
				'from'       => $from_email,
				'to'         => $formatted_emails,
				'subject'    => $data['subject'],
				'text'       => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'html'       => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'h:Reply-To' => empty ( $data['reply_to'] ) ? '' : $data['reply_to'],
				'tags'	     => empty( $data['email_tags'] ) ? [] :  $data['email_tags'],
				'multipart'  => true,
			);

			$sendlayer->sendMessage($messsage);
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		/* Send confirmation email */
		if ( ! empty( $data['send_confirmation'] ) && !empty($data['sender_email']) ) {
			try {
				$messsage = array(
					'name'		 => $from_email,
					'from'       => $from_email,
					'to'         => array(
						array( 'email' => $data['sender_email'] )
					),
					'subject'    => $data['confirmation_subject'],
					'text'       => '',
					'html'       => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'h:Reply-To' => $from_email,
					'multipart'  => true,
				);

				$sendlayer->sendMessage( $messsage );

			} catch ( Exception $e ) {
				return $e->getMessage();
			}
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		$endpoint = "https://console.sendlayer.com/api";

		return new Thrive_Dash_Api_SendLayer( $this->param( 'key' ), $endpoint );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * used for abstract class
	 */
	protected function _get_lists() {

	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * used for abstract class
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

	}
}
