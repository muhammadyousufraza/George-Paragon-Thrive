<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_MailRelayEmail extends Thrive_Dash_List_Connection_Abstract {

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
	 * @return string
	 */
	public function get_title() {
		return 'MailRelay';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'mailrelayemail' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {
		$connection = $this->post( 'connection' );
		$key        = ! empty( $connection['key'] ) ? $connection['key'] : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid MailRelay key', 'thrive-dash' ) );
		}

		$connection['domain'] = isset( $connection['url'] ) ? $connection['url'] : $connection['domain'];

		$url = ! empty( $connection['domain'] ) ? $connection['domain'] : '';

		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false || empty( $url ) ) {
			return $this->error( __( 'You must provide a valid MailRelay URL', 'thrive-dash' ) );
		}

		$this->set_credentials( $connection );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to MailRelay using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/**
		 * Try to connect to the autoresponder too
		 */
		/** @var Thrive_Dash_List_Connection_MailRelay $related_api */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'mailrelay' );

		$r_result = true;
		if ( ! $related_api->is_connected() ) {
			$_POST['connection']                   = $connection;
			$_POST['connection']['new_connection'] = isset( $connection['new_connection'] ) ? absint( $connection['new_connection'] ) : 1;

			$r_result = $related_api->read_credentials();
		}

		if ( $r_result !== true ) {
			$this->disconnect();

			return $this->error( $r_result );
		}

		return $this->success( __( 'MailRelay connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_MailRelay $mr */

		$mr = $this->get_api();

		$email = get_option( 'admin_email' );

		$args = array(
			'subject' => 'API connection test',
			'html'    => 'This is a test email from Thrive Leads MailRelay API.',
			'emails'  => array(
				array(
					'name'  => '',
					'email' => $email,
				),
			),
		);

		try {
			$mr->sendEmail( $args );
		} catch ( Thrive_Dash_Api_MailRelay_Exception $e ) {
			return $e->getMessage();
		}

		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'mailrelayemail' );
		}

		return true;
	}

	/**
	 * Send custom email
	 *
	 * @param $data
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function sendCustomEmail( $data ) {
		$mr = $this->get_api();

		try {

			$message = array(
				'html'    => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'subject' => $data['subject'],
				'emails'  => array(
					array(
						'email' => $data['email'],
						'name'  => '',
					),
				),
			);

			$mr->sendEmail( $message );

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
		$mr = $this->get_api();

		/**
		 * prepare $to
		 */
		$to           = array();
		$extra_emails = array();

		if ( isset( $data['cc'] ) ) {
			$extra_emails = $data['cc'];
		}

		if ( isset( $data['bcc'] ) ) {
			$extra_emails = array_merge( $extra_emails, $data['bcc'] );
		}

		$emails = is_array( $extra_emails ) ? array_merge( $data['emails'], $extra_emails ) : $data['emails'];
		foreach ( $emails as $email ) {
			$temp = array(
				'email' => $email,
				'name'  => '',
			);
			$to[] = $temp;
		}

		try {

			$message = array(
				'html'    => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'subject' => $data['subject'],
				'emails'  => $to,
			);

			$mr->sendEmail( $message );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		if ( ! empty( $data['send_confirmation'] ) ) {
			try {

				$message = array(
					'html'    => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'subject' => $data['confirmation_subject'],
					'emails'  => array(
						array(
							'email' => $data['sender_email'],
							'name'  => '',
						),
					),
				);

				$mr->sendEmail( $message );

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
		$mr = $this->get_api();

		$asset = get_post( $post_data['_asset_group'] );

		if ( empty( $asset ) || ! ( $asset instanceof WP_Post ) || $asset->post_status !== 'publish' ) {
			throw new Exception( sprintf( __( 'Invalid Asset Group: %s. Check if it exists or was trashed.', 'thrive-dash' ), $post_data['_asset_group'] ) );
		}

		$files   = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_files', true );
		$subject = get_post_meta( $post_data['_asset_group'], 'tve_asset_group_subject', true );

		if ( $subject == "" ) {
			$subject = get_option( 'tve_leads_asset_mail_subject' );
		}

		$credentials = Thrive_Dash_List_Manager::credentials( 'mailrelayemail' );

		if ( isset( $credentials ) ) {
			$from_email = get_option( 'admin_email' );
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
			$html_content = str_replace( '[lead_name]', $post_data['name'], $html_content );
			$subject      = str_replace( '[lead_name]', $post_data['name'], $subject );
			$visitor_name = $post_data['name'];
		} else {
			$html_content = str_replace( '[lead_name]', '', $html_content );
			$subject      = str_replace( '[lead_name]', '', $subject );
			$visitor_name = '';
		}
		$html_content = str_replace( '"', "'", $html_content );
		$message      = array(
			'subject' => $subject,
			'html'    => $html_content,
			'emails'  => array(
				array(
					'name'  => $visitor_name,
					'email' => $post_data['email'],
				),
			),

		);

		$result = $mr->sendEmail( $message );

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {

		if ( false !== strpos( $this->param( 'domain' ), 'ipzmarketing' ) ) {
			$instance = new Thrive_Dash_Api_MailRelayV1( $this->param( 'domain' ), $this->param( 'key' ) );
		} else {
			$instance = new Thrive_Dash_Api_MailRelay( array(
				'host'   => $this->param( 'domain' ),
				'apiKey' => $this->param( 'key' ),
			) );
		}

		return $instance;
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _get_lists() {

	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|string true for success or string error message for failure
	 */
	public function add_subscriber( $list_identifier, $arguments ) {


	}
}
