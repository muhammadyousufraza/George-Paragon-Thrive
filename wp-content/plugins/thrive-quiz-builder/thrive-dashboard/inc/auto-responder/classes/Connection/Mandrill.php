<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Mandrill extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * Return if the connection is in relation with another connection so we won't show it in the API list
	 *
	 * @return bool
	 */
	public function is_related() {
		return true;
	}

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
		return 'Mandrill';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'mailchimp' );
		if ( $related_api->is_connected() ) {
			$credentials = $related_api->get_credentials();
			$this->set_param( 'mailchimp_key', $credentials['key'] );
		}
		$this->output_controls_html( 'mandrill' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {

		$mailchimp_key = ! empty( $_POST['connection']['mailchimp_key'] ) ? sanitize_text_field( $_POST['connection']['mailchimp_key'] ) : '';
		$email         = ! empty( $_POST['connection']['email'] ) ? sanitize_text_field( $_POST['connection']['email'] ) : '';

		if ( isset( $_POST['connection']['mandrill-key'] ) && isset( $_POST['connection']['key'] ) ) {
			$_POST['connection']['mailchimp_key'] = sanitize_text_field( $_POST['connection']['key'] );
			$_POST['connection']['key']           = sanitize_text_field( $_POST['connection']['mandrill-key'] );
		}

		if ( empty( $_POST['connection']['key'] ) ) {
			return $this->error( __( 'You must provide a valid Mandrill key', 'thrive-dash' ) );
		}

		if ( empty( $email ) ) {
			return $this->error( __( 'Email field must not be empty', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();
		if ( $result !== true ) {
			/**
			 * Doing this because Mandrill devs are retarded and because it's unprofessional for the end user to read 'gibberish'
			 */
			preg_match( '#"?(.+?)\{(.+)\}(")?$#', json_decode( $result ), $matches );
			if ( ! empty( $matches ) ) {
				$result  = json_decode( '{' . $matches[2] . '}', true );
				$message = false;
				foreach ( $result as $level1 ) {
					foreach ( $level1 as $level2 ) {
						if ( ! $message ) {
							$message = $level2;
						}
					}
				}
			} else {
				$message = $result;
			}

			return $this->error( sprintf( __( 'Could not connect to Mandrill using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $message ? $message : '' ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		if ( ! empty( $mailchimp_key ) ) {
			/**
			 * Try to connect to the email service too
			 */
			/** @var Thrive_Dash_List_Connection_Mandrill $related_api */
			$related_api = Thrive_Dash_List_Manager::connection_instance( 'mailchimp' );
			$r_result    = true;
			if ( ! $related_api->is_connected() ) {
				$r_result = $related_api->read_credentials();
			}

			if ( $r_result !== true ) {
				$this->disconnect();

				return $this->error( $r_result );
			}
		}

		return $this->success( __( 'Mandrill connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$mandrill = $this->get_api();

		if ( isset( $_POST['connection']['email'] ) ) {
			$from_email = sanitize_text_field( $_POST['connection']['email'] );
			$to         = $from_email;
		} else {
			$credentials = Thrive_Dash_List_Manager::credentials( 'mandrill' );
			if ( isset( $credentials ) ) {
				$from_email = $credentials['email'];
				$to         = $credentials['email'];
			} else {
				return false;
			}
		}

		$subject      = 'API connection test';
		$html_content = 'This is a test email from Thrive Leads Mandrill API.';
		$text_content = 'This is a test email from Thrive Leads Mandrill API.';

		$message = array(
			'html'           => $html_content,
			'text'           => $text_content,
			'subject'        => $subject,
			'from_email'     => $from_email,
			'from_name'      => '',
			'to'             => array(
				array(
					'email' => $to,
					'name'  => '',
					'type'  => 'to',
				),
			),
			'headers'        => array( 'Reply-To' => $from_email ),
			'merge'          => true,
			'merge_language' => 'mailchimp',

		);
		$async   = false;
		$ip_pool = 'Main Pool';


		try {
			$result = $mandrill->messages->send( $message, $async, $ip_pool );
			if ( isset( $result['body'] ) ) {
				$body = json_decode( $result['body'] );
				$body = $body[0];
				if ( $body->status == 'rejected' ) {
					if ( $body->reject_reason == 'unsigned' ) {
						return $this->error( __( "The email filled in was not verified by Mandrill", 'thrive-dash' ) );
					}

					return $this->error( __( "Mandrill couldn't connect", 'thrive-dash' ) );
				}
			}
		} catch ( Thrive_Dash_Api_Mandrill_Exceptions $e ) {
			return $e->getMessage();
		}
		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'mandrill' );
		}

		return true;

		/**
		 * just try getting a list as a connection test
		 */
	}

	/**
	 * Send custom email
	 *
	 * @param $data
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function sendCustomEmail( $data ) {
		$mandrill = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'mandrill' );

		if ( isset( $credentials ) ) {
			$from_email = $credentials['email'];
		} else {
			return false;
		}

		try {

			$message = array(
				'html'           => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'text'           => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'subject'        => $data['subject'],
				'from_email'     => $from_email,
				'from_name'      => '',
				'to'             => array(
					array(
						'email' => $data['email'],
						'name'  => '',
						'type'  => 'to',
					),
				),
				'headers'        => array( 'Reply-To' => $from_email ),
				'merge'          => true,
				'merge_language' => 'mailchimp',

			);
			$async   = false;
			$ip_pool = 'Main Pool';

			$mandrill->messages->send( $message, $async, $ip_pool );

		} catch ( Exception $e ) {
			return $e->getMessage();
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
		$mandrill = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'mandrill' );

		if ( isset( $credentials ) ) {
			$from_email = $credentials['email'];
		} else {
			return false;
		}

		/**
		 * prepare $to
		 */
		$to  = array();
		$cc  = isset( $data['cc'] ) ? $data['cc'] : array();
		$bcc = isset( $data['bcc'] ) ? $data['bcc'] : array();

		foreach ( $data['emails'] as $email ) {
			$temp = array(
				'email' => $email,
				'name'  => '',
				'type'  => 'to',
			);
			$to[] = $temp;
		}

		foreach ( $cc as $email ) {
			$temp = array(
				'email' => $email,
				'name'  => '',
				'type'  => 'cc',
			);
			$to[] = $temp;
		}

		foreach ( $bcc as $email ) {
			$temp = array(
				'email' => $email,
				'name'  => '',
				'type'  => 'bcc',
			);
			$to[] = $temp;
		}

		try {

			$message = array(
				'html'           => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'text'           => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'subject'        => $data['subject'],
				'from_email'     => $from_email,
				'from_name'      => ! empty( $data['from_name'] ) ? $data['from_name'] : '',
				'to'             => $to,
				'headers'        => array( 'Reply-To' => empty ( $data['reply_to'] ) ? '' : $data['reply_to'] ),
				'merge'          => true,
				'merge_language' => 'mailchimp',
			);

			$async   = false;
			$ip_pool = 'Main Pool';

			$mandrill->messages->send( $message, $async, $ip_pool );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		if ( ! empty( $data['send_confirmation'] ) ) {

			try {

				$message = array(
					'html'           => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'text'           => '',
					'subject'        => $data['confirmation_subject'],
					'from_email'     => $from_email,
					'from_name'      => ! empty( $data['from_name'] ) ? $data['from_name'] : '',
					'to'             => array(
						array(
							'email' => $data['sender_email'],
							'name'  => '',
							'type'  => 'to',
						),
					),
					'headers'        => array( 'Reply-To' => $from_email ),
					'merge'          => true,
					'merge_language' => 'mailchimp',
				);

				$async   = false;
				$ip_pool = 'Main Pool';

				$mandrill->messages->send( $message, $async, $ip_pool );

			} catch ( Exception $e ) {
				return $e->getMessage();
			}
		}


		return true;
	}

	/**
	 * Send the email to the user
	 *
	 * @param $post_data
	 *
	 * @return bool|string
	 * @throws Exception
	 *
	 */
	public function sendEmail( $post_data ) {
		$mandrill = $this->get_api();

		$asset = get_post( $post_data['_asset_group'] );

		if ( empty( $asset ) || ! ( $asset instanceof WP_Post ) || $asset->post_status !== 'publish' ) {
			throw new Exception( sprintf( __( 'Invalid Asset Group: %s. Check if it exists or was trashed.', 'thrive-dash' ), $post_data['_asset_group'] ) );
		}

		$files   = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_files', true );
		$subject = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_subject', true );

		if ( $subject == "" ) {
			$subject = get_option( 'tve_leads_asset_mail_subject' );
		}

		$credentials = Thrive_Dash_List_Manager::credentials( 'mandrill' );
		if ( isset( $credentials ) ) {
			$from_email = $credentials['email'];
		} else {
			return false;
		}

		$html_content = $asset->post_content;

		if ( $html_content == "" ) {
			$html_content = get_option( 'tve_leads_asset_mail_body' );
		}

		$attached_files = array();
		foreach ( $files as $file ) {
			$attached_files[] = '<a href="' . $file['link'] . '">' . $file['link_anchor'] . '</a><br/>';
		}
		$the_files = implode( '<br/>', $attached_files );

		$html_content = str_replace( '[asset_download]', $the_files, $html_content );
		$html_content = str_replace( '[asset_name]', $asset->post_title, $html_content );
		$subject      = str_replace( '[asset_name]', $asset->post_title, $subject );

		if ( isset( $post_data['name'] ) && ! empty( $post_data['name'] ) ) {
			$from_name    = $post_data['name'];
			$html_content = str_replace( '[lead_name]', $post_data['name'], $html_content );
			$subject      = str_replace( '[lead_name]', $post_data['name'], $subject );
			$visitor_name = $post_data['name'];
		} else {
			$from_name    = "";
			$html_content = str_replace( '[lead_name]', '', $html_content );
			$subject      = str_replace( '[lead_name]', '', $subject );
			$visitor_name = '';
		}

		$text_content = strip_tags( $html_content );

		$message = array(
			'html'           => $html_content,
			'text'           => $text_content,
			'subject'        => $subject,
			'from_email'     => $from_email,
			'from_name'      => '',
			'to'             => array(
				array(
					'email' => $post_data['email'],
					'name'  => $visitor_name,
					'type'  => 'to',
				),
			),
			'headers'        => array( 'Reply-To' => $from_email ),
			'merge'          => true,
			'merge_language' => 'mailchimp',

		);
		$async   = false;
		$ip_pool = 'Main Pool';


		$result = $mandrill->messages->send( $message, $async, $ip_pool );

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Mandrill( $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {

	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

	}
}
