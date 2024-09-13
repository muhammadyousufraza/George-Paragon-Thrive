<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Mailgun extends Thrive_Dash_List_Connection_Abstract {
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
		return 'Mailgun';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'mailgun' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {
		$ajax_call = defined( 'DOING_AJAX' ) && DOING_AJAX;

		$key    = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';
		$domain = ! empty( $_POST['connection']['domain'] ) ? sanitize_text_field( $_POST['connection']['domain'] ) : '';
		$zone   = ! empty( $_POST['connection']['zone'] ) ? sanitize_text_field( $_POST['connection']['zone'] ) : '';

		if ( empty( $key ) ) {
			return $ajax_call ? __( 'You must provide a valid Mailgun key', 'thrive-dash' ) : $this->error( __( 'You must provide a valid Mailgun key', 'thrive-dash' ) );
		}

		if ( empty( $domain ) ) {
			return $ajax_call ? __( 'The domain name field must not be empty', 'thrive-dash' ) : $this->error( __( 'The domain name field must not be empty', 'thrive-dash' ) );
		}

		$this->set_credentials( compact( 'key', 'domain', 'zone' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $ajax_call ? sprintf( __( 'Could not connect to Mailgun using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) : $this->error( sprintf( __( 'Could not connect to Mailgun using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();
		$this->success( __( 'Mailgun connected successfully', 'thrive-dash' ) );

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
		/** @var Thrive_Dash_Api_Mailgun $mailgun */
		$mailgun = $this->get_api();

		if ( isset( $_POST['connection']['domain'] ) ) {
			$domain = sanitize_text_field( $_POST['connection']['domain'] );
		} else {
			$credentials = Thrive_Dash_List_Manager::credentials( 'mailgun' );
			if ( isset( $credentials ) ) {
				$domain = $credentials['domain'];
			}
		}

		$from_email = get_option( 'admin_email' );
		$to         = $from_email;

		$subject      = 'API connection test';
		$text_content = $html_content = 'This is a test email from Thrive Leads Mailgun API.';

		try {
			$mailgun->sendMessage( "$domain",
				array(
					'from'      => $from_email,
					'to'        => $to,
					'subject'   => $subject,
					'text'      => $text_content,
					'html'      => $html_content,
					'multipart' => true,
				) );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}
		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'mailgun' );
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
		$mailgun = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'mailgun' );
		if ( isset( $credentials ) ) {
			$domain = $credentials['domain'];
		} else {
			return false;
		}
		$from_email = get_option( 'admin_email' );
		try {
			$messsage = array(
				'from'      => $from_email,
				'to'        => $data['email'],
				'subject'   => $data['subject'],
				'text'      => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'html'      => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'multipart' => true,
			);

			$mailgun->sendMessage( "$domain", $messsage );

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
		$mailgun = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'mailgun' );
		if ( isset( $credentials ) ) {
			$domain = $credentials['domain'];
		} else {
			return false;
		}
		$from_email = get_option( 'admin_email' );
		try {
			$messsage = array(
				'from'       => $from_email,
				'to'         => $data['emails'],
				'subject'    => $data['subject'],
				'text'       => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'html'       => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'h:Reply-To' => empty ( $data['reply_to'] ) ? '' : $data['reply_to'],
				'multipart'  => true,
			);

			$mailgun->sendMessage( "$domain", $messsage );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}
		/* Send confirmation email */
		if ( ! empty( $data['send_confirmation'] ) ) {
			try {
				$messsage = array(
					'from'       => $from_email,
					'to'         => array( $data['sender_email'] ),
					'subject'    => $data['confirmation_subject'],
					'text'       => '',
					'html'       => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'h:Reply-To' => $from_email,
					'multipart'  => true,
				);

				$mailgun->sendMessage( "$domain", $messsage );

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

		$mailgun = $this->get_api();

		$asset = get_post( $post_data['_asset_group'] );

		if ( empty( $asset ) || ! ( $asset instanceof WP_Post ) || $asset->post_status !== 'publish' ) {
			throw new Exception( sprintf( __( 'Invalid Asset Group: %s. Check if it exists or was trashed.', 'thrive-dash' ), $post_data['_asset_group'] ) );
		}

		$files   = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_files', true );
		$subject = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_subject', true );

		if ( $subject == "" ) {
			$subject = get_option( 'tve_leads_asset_mail_subject' );
		}
		$from_email   = get_option( 'admin_email' );
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
			$from_name    = '<' . $post_data['name'] . '>';
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

		$credentials = Thrive_Dash_List_Manager::credentials( 'mailgun' );
		if ( isset( $credentials ) ) {
			$domain = $credentials['domain'];
		}

		$result = $mailgun->sendMessage( "$domain",
			array(
				'from'      => $from_email,
				'to'        => $visitor_name . "<" . $post_data['email'] . ">",
				'subject'   => $subject,
				'text'      => $text_content,
				'html'      => $html_content,
				'multipart' => true,
			) );

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		$zone     = $this->param( 'zone' );
		$endpoint = $zone && $zone === 'europe' ? 'api.eu.mailgun.net' : 'api.mailgun.net';

		return new Thrive_Dash_Api_Mailgun( $this->param( 'key' ), $endpoint );
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
