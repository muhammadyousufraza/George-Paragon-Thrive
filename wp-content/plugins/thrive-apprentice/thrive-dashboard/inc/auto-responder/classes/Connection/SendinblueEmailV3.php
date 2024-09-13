<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}


class Thrive_Dash_List_Connection_SendinblueEmailV3 extends Thrive_Dash_List_Connection_SendinblueEmail {
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
	 * Output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sendinblueemail' );
	}

	/**
	 * Should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 */
	public function read_credentials() {
		$ajax_call = defined( 'DOING_AJAX' ) && DOING_AJAX;

		$key = ! empty( $_POST['connection']['key'] ) ? $_POST['connection']['key'] : '';

		if ( empty( $key ) ) {
			$message = 'You must provide a valid Brevo key';

			return $ajax_call ? __( $message, 'thrive-dash' ) : $this->error( __( $message, 'thrive-dash' ) );
		}
		/* For V3 we need to add this on the credentials list */
		$_POST['connection']['v3'] = true;
		$this->set_credentials( $_POST['connection'] );

		$result = $this->test_connection();

		if ( $result !== true ) {
			$message = 'Could not connect to Brevo using the provided key (<strong>%s</strong>)';

			return $ajax_call ? sprintf( __( $message, 'thrive-dash' ), $result ) : $this->error( sprintf( __( $message, 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/**
		 * Try to connect to the autoresponder too
		 */
		/** @var Thrive_Dash_List_Connection_SendinblueV3 $related_api */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'sendinblue' );

		$response = true;
		if ( ! $related_api->is_connected() ) {
			$_POST['connection']['new_connection'] = isset( $_POST['connection']['new_connection'] ) ? $_POST['connection']['new_connection'] : 1;

			$response = $related_api->read_credentials();
		}

		if ( $response !== true ) {
			$this->disconnect();

			return $this->error( $response );
		}

		$this->success( __( 'SendinBlue connected successfully', 'thrive-dash' ) );

		if ( $ajax_call ) {
			return true;
		}
	}

	/**
	 * Test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		if ( ! $this->is_v3() ) {
			return parent::test_connection();
		}
		$sendinblue = $this->get_api();

		$from_email   = get_option( 'admin_email' );
		$to           = $from_email;
		$subject      = 'API connection test';
		$html_content = 'This is a test email from Thrive Leads SendinBlue API.';
		$text_content = $html_content;

		try {
			$data = array(
				'to'          => array( array( 'email' => $to ) ),
				'sender'      => array( 'email' => $from_email ),
				'subject'     => $subject,
				'htmlContent' => $html_content,
				'textContent' => $text_content,
			);

			$sendinblue->send_email( $data );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		$connection = get_option( 'tve_api_delivery_service', false );

		if ( $connection === false ) {
			update_option( 'tve_api_delivery_service', 'sendinblueemail-v3' );
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
		if ( ! $this->is_v3() ) {
			return parent::sendCustomEmail( $data );
		}
		$sendinblue = $this->get_api();

		$from_email = get_option( 'admin_email' );

		try {
			$options = array(
				'to'      => array( array( 'email' => $data['email'] ) ),
				'sender'  => array( 'email' => $from_email ),
				'subject' => $data['subject'],
			);

			if ( ! empty ( $data['html_content'] ) ) {
				$options['htmlContent'] = $data['html_content'];
			}

			if ( ! empty ( $data['text_content'] ) ) {
				$options['textContent'] = $data['text_content'];
			}

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
		if ( ! $this->is_v3() ) {
			return parent::sendMultipleEmails( $data );
		}
		$sendinblue = $this->get_api();

		$from_email   = get_option( 'admin_email' );
		$to           = array();
		$extra_emails = array();

		foreach ( array_merge( $data['emails'], $extra_emails ) as $email ) {
			$to[] = array( 'email' => $email );
		}

		try {
			$options = array(
				'to'          => $to,
				'sender'      => array( 'email' => $from_email ),
				'subject'     => $data['subject'],
				'htmlContent' => empty ( $data['html_content'] ) ? '' : $data['html_content'],
			);

			if ( ! empty ( $data['text_content'] ) ) {
				$options['textContent'] = $data['text_content'];
			}
			if ( ! empty( $data['from_name'] ) ) {
				$options['sender']['name'] = $data['from_name'];
			}

			if ( ! empty( $data['reply_to'] ) ) {
				$options['reply_to'] = $data['reply_to'];
			}

			if ( ! empty( $data['cc'] ) ) {
				$options['cc'] = $data['cc'];
			}

			if ( ! empty( $data['bcc'] ) ) {
				$options['bcc'] = $data['bcc'];
			}

			$sendinblue->send_email( $options );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		/* Send confirmation email */
		if ( ! empty( $data['send_confirmation'] ) ) {
			try {
				$options = array(
					'to'          => array( array( 'email' => $data['sender_email'] ) ),
					'sender'      => array( 'email' => $from_email ),
					'subject'     => $data['confirmation_subject'],
					'htmlContent' => empty ( $data['confirmation_html'] ) ? '' : $data['confirmation_html'],
					'replyTo'     => array( 'email' => $from_email ),
				);
				if ( ! empty( $data['from_name'] ) ) {
					$options['sender']['name'] = $data['from_name'];
				}
				if ( ! empty ( $data['text_content'] ) ) {
					$options['textContent'] = $data['text_content'];
				}

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
		if ( ! $this->is_v3() ) {
			return parent::sendEmail( $post_data );
		}
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

		if ( $subject === '' ) {
			$subject = get_option( 'tve_leads_asset_mail_subject' );
		}

		$from_email   = get_option( 'admin_email' );
		$html_content = $asset->post_content;

		if ( $html_content === '' ) {
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

		$visitor_name = isset( $post_data['name'] ) && ! empty( $post_data['name'] ) ? $post_data['name'] : '';
		$html_content = str_replace( '[lead_name]', $visitor_name, $html_content );
		$subject      = str_replace( '[lead_name]', $visitor_name, $subject );

		$text_content = strip_tags( $html_content );
		$to           = array( 'email' => $post_data['email'] );

		if ( ! empty( $visitor_name ) ) {
			$to['name'] = $visitor_name;
		}

		$data = array(
			'to'          => array( $to ),
			'sender'      => array( 'email' => $from_email ),
			'subject'     => $subject,
			'htmlContent' => $html_content,
			'textContent' => $text_content,
		);


		return $sendinblue->send_email( $data );
	}

	/**
	 * Instantiate the API code required for this connection
	 *
	 * @return Thrive_Dash_Api_SendinblueV3
	 * @throws Exception
	 */
	protected function get_api_instance() {
		if ( ! $this->is_v3() ) {
			return parent::get_api_instance();
		}

		return new Thrive_Dash_Api_SendinblueV3( 'https://api.sendinblue.com/v3', $this->param( 'key' ) );
	}

	/**
	 * Get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		if ( ! $this->is_v3() ) {
			return parent::_get_lists();
		}
		$sendinblue = $this->get_api();

		$limit  = 50;
		$offset = 1;
		try {
			$lists = array();

			$raw = $sendinblue->getLists( $limit, $offset );

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
			$this->_error = $e->getMessage() . ' ' . __( 'Please re-check your API connection details.', 'thrive-dash' );

			return false;
		}
	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|string|void
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		if ( ! $this->is_v3() ) {
			return parent::add_subscriber( $list_identifier, $arguments );
		}
		list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

		$api = $this->get_api();

		$merge_tags = array(
			'NAME'    => $first_name,
			'SURNAME' => $last_name,
		);

		$data = array(
			'email'      => $arguments['email'],
			'attributes' => $merge_tags,
			'listid'     => array( $list_identifier ),
		);

		try {
			$api->create_update_user( $data );

			return true;
		} catch ( Thrive_Dash_Api_SendinBlue_Exception $e ) {
			return $e->getMessage() ?: __( 'Unknown SendinBlue Error', 'thrive-dash' );
		} catch ( Exception $e ) {
			return $e->getMessage() ?: __( 'Unknown Error', 'thrive-dash' );
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
}
