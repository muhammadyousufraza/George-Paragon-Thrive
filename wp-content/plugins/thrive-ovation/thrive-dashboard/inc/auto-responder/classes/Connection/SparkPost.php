<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_SparkPost extends Thrive_Dash_List_Connection_Abstract {

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
		return 'SparkPost';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sparkpost' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {

		$key   = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';
		$email = ! empty( $_POST['connection']['domain'] ) ? sanitize_text_field( $_POST['connection']['domain'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid SparkPost key', 'thrive-dash' ) );
		}

		if ( empty( $email ) ) {
			return $this->error( __( 'Email field must not be empty', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();


		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to SparkPost using the provided key. <strong>%s</strong>', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'SparkPost connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$sparkpost = $this->get_api();

		if ( isset( $_POST['connection']['domain'] ) ) {
			$domain = sanitize_text_field( $_POST['connection']['domain'] );
		} else {
			$credentials = Thrive_Dash_List_Manager::credentials( 'sparkpost' );
			if ( isset( $credentials ) ) {
				$domain = $credentials['domain'];
			}
		}
		$to           = get_option( 'admin_email' );
		$subject      = 'API connection test';
		$html_content = 'This is a test email from Thrive Leads SparkPost API.';
		$text_content = 'This is a test email from Thrive Leads SparkPost API.';

		try {
			$options = array(
				'from'       => $domain,
				'html'       => $html_content,
				'text'       => $text_content,
				'subject'    => $subject,
				'recipients' => array(
					array(
						'address' => array(
							'email' => $to,
						),
					),
				),
			);
			$sparkpost->transmission->send( $options );
		} catch ( Thrive_Dash_Api_SparkPost_Exception $e ) {
			return $e->getMessage();
		}

		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'sparkpost' );
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
		$sparkpost = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'sparkpost' );
		if ( isset( $credentials ) ) {
			$domain = $credentials['domain'];
		}

		try {
			$options = array(
				'from'       => $domain,
				'html'       => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'text'       => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'subject'    => $data['subject'],
				'recipients' => array(
					array(
						'address' => array(
							'email' => $data['email'],
						),
					),
				),
			);
			$sparkpost->transmission->send( $options );
		} catch ( Thrive_Dash_Api_SparkPost_Exception $e ) {
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
		$sparkpost = $this->get_api();

		$credentials = Thrive_Dash_List_Manager::credentials( 'sparkpost' );
		if ( isset( $credentials ) ) {
			$from_email = $credentials['domain'];
		}

		$recipients = $this->_prepareRecipients( $data );

		if ( ! empty( $data['from_name'] ) ) {
			$domain = [
				'name'  => $data['from_name'],
				'email' => $from_email,
			];
		} else {
			$domain = $from_email;
		}

		try {
			$options = array(
				'from'       => $domain,
				'html'       => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'text'       => empty ( $data['text_content'] ) ? '' : $data['text_content'],
				'subject'    => $data['subject'],
				'recipients' => $recipients,
			);

			if ( ! empty( $data['reply_to'] ) ) {
				$options['replyTo'] = $data['reply_to'];
			}

			$sparkpost->transmission->send( $options );
		} catch ( Thrive_Dash_Api_SparkPost_Exception $e ) {
			return $e->getMessage();
		}

		if ( ! empty( $data['send_confirmation'] ) ) {
			try {
				$confirmation = array(
					'from'       => $domain,
					'html'       => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'text'       => '',
					'subject'    => $data['confirmation_subject'],
					'recipients' => array(
						array(
							'address' => array(
								'email' => $data['sender_email'],
							),
						),
					),
					'replyTo'    => $from_email,
				);

				$sparkpost->transmission->send( $confirmation );
			} catch ( Thrive_Dash_Api_SparkPost_Exception $e ) {
				return $e->getMessage();
			}
		}

		return true;
	}

	/**
	 * Prepare email recipients
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function _prepareRecipients( $data ) {
		$recipients  = array();
		$first_email = current( $data['emails'] );


		$data['cc']  = ! empty( $data['cc'] ) ? $data['cc'] : array();
		$data['bcc'] = ! empty( $data['bcc'] ) ? $data['bcc'] : array();
		$extra       = array_merge( $data['cc'], $data['bcc'] );

		foreach ( array_unique( $data['emails'] ) as $email ) {
			$recipients[] = array(
				'address' => array(
					'email' => $email,
				),
			);
		}

		foreach ( array_unique( $extra ) as $email ) {
			$recipients[] = array(
				'address' => array(
					'email'     => $email,
					'header_to' => $first_email,
				),
			);
		}

		return $recipients;
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
		$sparkpost   = $this->get_api();
		$credentials = $this->get_credentials();

		if ( empty( $post_data['_asset_group'] ) ) {
			return false;
		}

		$asset = get_post( $post_data['_asset_group'] );

		if ( empty( $asset ) || ! ( $asset instanceof WP_Post ) || $asset->post_status !== 'publish' ) {
			throw new Exception( sprintf( __( 'Invalid Asset Group: %s. Check if it exists or was trashed.', 'thrive-dash' ), $post_data['_asset_group'] ) );
		}

		$files   = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_files', true );
		$subject = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_subject', true );

		if ( $subject == "" ) {
			$subject = get_option( 'tve_leads_asset_mail_subject' );
		}
		$from         = $credentials['domain'];
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
		$options      = array(
			'from'       => $from,
			'html'       => $html_content,
			'text'       => $text_content,
			'subject'    => $subject,
			'options'    => array(),
			'recipients' => array(
				array(
					'address' => array(
						'email' => $post_data['email'],
					),
				),
			),
		);

		$result = $sparkpost->transmission->send( $options );

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_SparkPost( $this->param( 'key' ) );
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

	/**
	 * Get from email value
	 *
	 * @return string
	 */
	public function get_email_param() {
		return $this->param( 'domain' );
	}
}
