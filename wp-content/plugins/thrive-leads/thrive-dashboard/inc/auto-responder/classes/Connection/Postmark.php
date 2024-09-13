<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Postmark extends Thrive_Dash_List_Connection_Abstract {
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
		return 'Postmark';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'postmark' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {
		$key   = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';
		$email = ! empty( $_POST['connection']['email'] ) ? sanitize_text_field( $_POST['connection']['email'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid Postmark key', 'thrive-dash' ) );
		}

		if ( empty( $email ) ) {
			return $this->error( __( 'Email field must not be empty', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();


		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to Postmark using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'Postmark connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$postmark = $this->get_api();

		if ( isset( $_POST['connection']['email'] ) ) {
			$from_email = sanitize_email( $_POST['connection']['email'] );
			$to         = $from_email;
		} else {
			$credentials = Thrive_Dash_List_Manager::credentials( 'postmark' );
			if ( isset( $credentials ) ) {
				$from_email = $credentials['email'];
				$to         = $credentials['email'];
			}
		}

		$subject      = 'API connection test';
		$html_content = 'This is a test email from Thrive Leads Postmark API.';
		$text_content = 'This is a test email from Thrive Leads Postmark API.';
		try {
			$postmark->sendEmail( $from_email, $to, $subject, $html_content, $text_content );
		} catch ( Thrive_Dash_Api_Postmark_Exception $e ) {
			return $e->getMessage();
		}

		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'postmark' );
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
		/**
		 * @var $postmark Thrive_Dash_Api_Postmark
		 */
		$postmark = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'postmark' );
		if ( is_array( $credentials ) && ! empty( $credentials['email'] ) ) {
			$from_email = $credentials['email'];
		} else {
			return false;
		}

		if ( ! empty( $data['from_name'] ) ) {
			$from_email = $data['from_name'] . ' < ' . $from_email . ' >';
		}

		try {
			$postmark->sendEmail(
				$from_email,
				$data['email'],
				$data['subject'],
				empty ( $data['html_content'] ) ? '' : $data['html_content'],
				empty ( $data['text_content'] ) ? '' : $data['text_content'],
				null,
				null,
				empty ( $data['reply_to'] ) ? '' : $data['reply_to'],
				empty( $data['cc'] ) ? '' : implode( ', ', $data['cc'] ),
				empty( $data['bcc'] ) ? '' : implode( ', ', $data['bcc'] )
			);
		} catch ( Thrive_Dash_Api_Postmark_Exception $e ) {
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
		/**
		 * we should never fucking do that
		 * pls find a way to send all the emails at once
		 */
		foreach ( $data['emails'] as $email ) {
			$data['email'] = $email;
			$this->sendCustomEmail( $data );
		}

		/**
		 * Send confirmation email
		 */
		if ( ! empty( $data['send_confirmation'] ) ) {
			$confirmation = array(
				'email'        => $data['sender_email'],
				'from_name'    => $data['from_name'],
				'html_content' => $data['confirmation_html'],
				'subject'      => $data['confirmation_subject'],
			);
			$credentials  = Thrive_Dash_List_Manager::credentials( 'postmark' );
			if ( is_array( $credentials ) && ! empty( $credentials['email'] ) ) {
				$confirmation['reply_to'] = $credentials['email'];
			}

			$this->sendCustomEmail( $confirmation );
		}
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
		$postmark    = $this->get_api();
		$credentials = $this->get_credentials();

		$asset = get_post( $post_data['_asset_group'] );

		if ( empty( $asset ) || ! ( $asset instanceof WP_Post ) || $asset->post_status !== 'publish' ) {
			throw new Exception( sprintf( __( 'Invalid Asset Group: %s. Check if it exists or was trashed.', 'thrive-dash' ), $post_data['_asset_group'] ) );
		}

		$files   = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_files', true );
		$subject = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_subject', true );

		if ( $subject == "" ) {
			$subject = get_option( 'tve_leads_asset_mail_subject' );
		}
		$from_email   = $credentials['email'];
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
			$html_content = str_replace( '[lead_name]', $post_data['name'], $html_content );
			$subject      = str_replace( '[lead_name]', $post_data['name'], $subject );
			$visitor_name = $post_data['name'];
		} else {
			$html_content = str_replace( '[lead_name]', '', $html_content );
			$subject      = str_replace( '[lead_name]', '', $subject );
			$visitor_name = '';
		}

		$text_content = strip_tags( $html_content );

		$result = $postmark->sendEmail(
			$from_email,
			$post_data['email'],
			$subject,
			$html_content,
			$text_content
		);

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Postmark( $this->param( 'key' ) );
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
