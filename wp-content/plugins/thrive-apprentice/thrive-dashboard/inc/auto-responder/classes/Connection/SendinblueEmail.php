<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_SendinblueEmail extends Thrive_Dash_List_Connection_Abstract {

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
		return 'SendinBlue';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sendinblueemail' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {
		$ajax_call = defined( 'DOING_AJAX' ) && DOING_AJAX;

		$key = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';

		if ( empty( $key ) ) {
			return $ajax_call ? __( 'You must provide a valid SendinBlue key', 'thrive-dash' ) : $this->error( __( 'You must provide a valid SendinBlue key', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $ajax_call ? sprintf( __( 'Could not connect to SendinBlue using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) : $this->error( sprintf( __( 'Could not connect to SendinBlue using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/**
		 * Try to connect to the autoresponder too
		 */
		/** @var Thrive_Dash_List_Connection_Sendinblue $related_api */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'sendinblue' );

		$r_result = true;
		if ( ! $related_api->is_connected() ) {
			$_POST['connection']['new_connection'] = isset( $_POST['connection']['new_connection'] ) ? absint( $_POST['connection']['new_connection'] ) : 1;

			$r_result = $related_api->read_credentials();
		}

		if ( $r_result !== true ) {
			$this->disconnect();

			return $this->error( $r_result );
		}

		$this->success( __( 'SendinBlue connected successfully', 'thrive-dash' ) );

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
		$sendinblue = $this->get_api();

		$from_email = get_option( 'admin_email' );
		$to         = $from_email;

		$subject      = 'API connection test';
		$html_content = 'This is a test email from Thrive Leads SendinBlue API.';
		$text_content = 'This is a test email from Thrive Leads SendinBlue API.';

		try {
			$data = array(
				"to"      => array( $to => "" ),
				"from"    => array( $from_email, "" ),
				"subject" => $subject,
				"html"    => $html_content,
				"text"    => $text_content,
			);

			$sendinblue->send_email( $data );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection == false ) {
			update_option( 'tve_api_delivery_service', 'sendinblueemail' );
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
		$sendinblue = $this->get_api();

		$from_email = get_option( 'admin_email' );

		try {
			$options = array(
				"to"      => array( $data['email'] => '' ),
				"from"    => array( $from_email, "" ),
				"subject" => $data['subject'],
				'html'    => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'text'    => empty ( $data['text_content'] ) ? '' : $data['text_content'],
			);
			$sendinblue->send_email( $options );
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
		$sendinblue = $this->get_api();

		$from_email = get_option( 'admin_email' );

		$to           = array();
		$extra_emails = array();

		if ( isset( $data['cc'] ) ) {
			$extra_emails = $data['cc'];
		}

		if ( isset( $data['bcc'] ) ) {
			$extra_emails = array_merge( $extra_emails, $data['bcc'] );
		}

		foreach ( array_merge( $data['emails'], $extra_emails ) as $email ) {
			$to[ $email ] = '';
		}

		try {
			$options = array(
				'to'      => $to,
				'from'    => array( $from_email, ! empty( $data['from_name'] ) ? '"' . $data['from_name'] . '"' : "" ),
				'subject' => $data['subject'],
				'html'    => empty ( $data['html_content'] ) ? '' : $data['html_content'],
				'replyto' => array( empty ( $data['reply_to'] ) ? '' : $data['reply_to'], "" ),
				'text'    => empty ( $data['text_content'] ) ? '' : $data['text_content'],
			);
			$sendinblue->send_email( $options );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		/* Send confirmation email */
		if ( ! empty( $data['send_confirmation'] ) ) {
			try {
				$options = array(
					'to'      => array( $data['sender_email'] => '' ),
					'from'    => array( $from_email, ! empty( $data['from_name'] ) ? '"' . $data['from_name'] . '"' : "" ),
					'subject' => $data['confirmation_subject'],
					'html'    => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'text'    => '',
					'replyto' => array( $from_email, '' ),
				);
				$sendinblue->send_email( $options );
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
		if ( empty( $post_data['_asset_group'] ) ) {
			return true;
		}

		$sendinblue = $this->get_api();

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

		$data = array(
			"to"      => array( $post_data['email'] => $visitor_name ),
			"from"    => array( $from_email, "" ),
			"subject" => $subject,
			"html"    => $html_content,
			"text"    => $text_content,
		);

		$result = $sendinblue->send_email( $data );

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Sendinblue( "https://api.sendinblue.com/v2.0", $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		$sendinblue = $this->get_api();

		$data = array(
			"page"       => 1,
			"page_limit" => 50,
		);

		try {
			$lists = array();

			$raw = $sendinblue->get_lists( $data );

			if ( empty( $raw['data'] ) ) {
				return array();
			}

			foreach ( $raw['data']['lists'] as $item ) {
				$lists [] = array(
					'id'   => $item['id'],
					'name' => $item['name'],
				);
			}

			return $lists;
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage() . ' ' . __( "Please re-check your API connection details.", 'thrive-dash' );

			return false;
		}
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
		list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

		$api = $this->get_api();

		$merge_tags = array(
			'NAME'    => $first_name,
			'SURNAME' => $last_name,
		);

		$data = array(
			"email"      => $arguments['email'],
			"attributes" => $merge_tags,
			"listid"     => array( $list_identifier ),
		);


		try {
			$api->create_update_user( $data );

			return true;
		} catch ( Thrive_Dash_Api_SendinBlue_Exception $e ) {
			return $e->getMessage() ? $e->getMessage() : __( 'Unknown SendinBlue Error', 'thrive-dash' );
		} catch ( Exception $e ) {
			return $e->getMessage() ? $e->getMessage() : __( 'Unknown Error', 'thrive-dash' );
		}

	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '{EMAIL}';
	}

	/**
	 * Checks if a connection is V3
	 *
	 * @return bool
	 */
	public function is_v3() {
		$is_v3 = $this->param( 'v3' );

		return ! empty( $is_v3 );
	}
}
