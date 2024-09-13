<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_GoToWebinar extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * @var string
	 */
	private $_consumer_key = 'Mtm8i2IdR2mOkAY3uVoW5f4TdGaBxpkY';

	/**
	 * @var string
	 */
	private $_consumer_secret = 'qjr8KW9Ga6G2AJjE';

	/**
	 * Return the connection type
	 *
	 * @return string
	 */
	public static function get_type() {
		return 'webinar';
	}

	/**
	 * Check if the expires_in field is in the past
	 * GoToWebinar auth access tokens expire after about one year
	 *
	 * @return bool
	 */
	public function isExpired() {
		if ( ! $this->is_connected() ) {
			return false;
		}

		$expires_in = $this->param( 'expires_in' );

		return time() > $expires_in;
	}

	/**
	 * get the expiry date and time user-friendly formatted
	 */
	public function getExpiryDate() {
		return date( 'l, F j, Y H:i:s', $this->param( 'expires_in' ) );
	}

	/**
	 * API connection title
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GoToWebinar';
	}

	/**
	 * @return string
	 */
	public function get_list_sub_title() {
		return __( 'Choose from the following upcoming webinars', 'thrive-dash' );
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'gotowebinar' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed|Thrive_Dash_List_Connection_Abstract
	 */
	public function read_credentials() {
		if ( empty( $_POST['gtw_email'] ) || empty( $_POST['gtw_password'] ) ) {
			return $this->error( __( 'Email and password are required', 'thrive-dash' ) );
		}

		$email    = $this->post( 'gtw_email' );
		$password = $this->post( 'gtw_password' );

		$v = array(
			'version'    => ! empty( $_POST['connection']['version'] ) ? sanitize_text_field( $_POST['connection']['version'] ) : '',
			'versioning' => ! empty( $_POST['connection']['versioning'] ) ? sanitize_text_field( $_POST['connection']['versioning'] ) : '',
			'username'   => $email,
			'password'   => $password,
		);

		/** @var Thrive_Dash_Api_GoToWebinar $api */
		$api = $this->get_api();

		try {

			// Login and setters
			$api->directLogin( $email, $password, $v );

			$credentials = $api->get_credentials();

			// Add inbox notification for v2 connection
			if ( TD_Inbox::instance()->api_is_connected( $this->get_key() ) && ! empty( $credentials['version'] ) && 2 === (int) $credentials['version'] && ! empty( $credentials['versioning'] ) ) {

				$this->add_notification( 'added_v2' );

				// Remove notification from api connection
				TVE_Dash_InboxManager::instance()->remove_api_connection( $this->get_key() );
			}

			// Set credentials
			$this->set_credentials( $credentials );

			// Save the connection details
			$this->save();

			return $this->success( __( 'GoToWebinar connected successfully', 'thrive-dash' ) );

		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			return $this->error( sprintf( __( 'Could not connect to GoToWebinar using the provided data (%s)', 'thrive-dash' ), $e->getMessage() ) );
		}
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function add_notification( $type = '' ) {

		if ( empty( $type ) ) {
			return false;
		}

		$message       = array();
		$inbox_manager = TVE_Dash_InboxManager::instance();

		switch ( $type ) {
			case 'added_v2':
				$message = array(
					'title' => __( 'Your GoToWebinar Connection has been Updated!', 'thrive-dash' ),
					'info'  => __(
						'Good job - you’ve just upgraded your GoToWebinar connection to 2.0. <br /><br />

							You don’t need to make any changes to your existing forms - they will carry on working as before. <br /><br /> 
							
							However, we highly recommend that you sign up through one of your webinar forms to make sure that everything is working as expected.<br /><br />
							
							If you experience any issues, let our <a href="https://thrivethemes.com/forums/forum/general-discussion/" target="_blank">support team</a> know and we’ll get to the bottom of this for you. <br /><br />
							
							From your team at Thrive Themes ',
                        'thrive-dash'
					),
					'type'  => TD_Inbox_Message::TYPE_INBOX,
				);

				break;
		}

		if ( empty( $message ) ) {
			return false;
		}

		try {
			$message_obj = new TD_Inbox_Message( $message );
			$inbox_manager->prepend( $message_obj );
			$inbox_manager->push_notifications();
		} catch ( Exception $e ) {
		}
	}

	/**
	 * @return mixed|string
	 */
	public function getUsername() {
		$credentials = (array) $this->get_credentials();
		if ( ! empty( $credentials['username'] ) ) {
			return $credentials['username'];
		}

		return '';
	}

	/**
	 * @return mixed|string
	 */
	public function getPassword() {
		$credentials = (array) $this->get_credentials();
		if ( ! empty( $credentials['password'] ) ) {
			return $credentials['password'];
		}

		return '';
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string
	 */
	public function test_connection() {

		try {
			/** @var Thrive_Dash_Api_GoToWebinar * */
			$api      = $this->get_api();
			$webinars = $api->getUpcomingWebinars();

			if ( ! empty( $webinars ) ) {
				return true;
			}

			return false;
		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|mixed|string
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

		/** @var Thrive_Dash_Api_GoToWebinar $api */
		$api   = $this->get_api();
		$phone = isset( $arguments['phone'] ) ? $arguments['phone'] : null;

		list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

		if ( empty( $last_name ) ) {
			$last_name = $first_name;
		}

		if ( empty( $first_name ) && empty( $last_name ) ) {
			list( $first_name, $last_name ) = $this->get_name_from_email( $arguments['email'] );
		}

		try {
			$api->registerToWebinar( $list_identifier, $first_name, $last_name, $arguments['email'], $phone );

			return true;
		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			return $e->getMessage();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 *
	 * @return int the number of days in which this token will expire
	 */
	public function expiresIn() {
		$expires_in = $this->param( 'expires_in' );
		$diff       = (int) ( ( $expires_in - time() ) / ( 3600 * 24 ) );

		return $diff;
	}

	/**
	 * No need for v2
	 */
	public function get_warnings() {
		return array();
	}

	/**
	 * @return mixed|Thrive_Dash_Api_GoToWebinar
	 * @throws Thrive_Dash_Api_GoToWebinar_Exception
	 */
	protected function get_api_instance() {

		$access_token  = null;
		$organizer_key = null;
		$settings      = array();

		if ( $this->is_connected() && ! $this->isExpired() ) {
			$access_token  = $this->param( 'access_token' );
			$organizer_key = $this->param( 'organizer_key' );
			$settings      = array(
				'version'       => $this->param( 'version' ),
				'versioning'    => $this->param( 'versioning' ), // used on class instances from [/v1/, /v2/ etc] namespace folder
				'expires_in'    => $this->param( 'expires_in' ),
				'auth_type'     => $this->param( 'auth_type' ),
				'refresh_token' => $this->param( 'refresh_token' ),
				'username'      => $this->param( 'username' ),
				'password'      => $this->param( 'password' ),
			);
		}

		return new Thrive_Dash_Api_GoToWebinar( base64_encode( $this->_consumer_key . ':' . $this->_consumer_secret ), $access_token, $organizer_key, $settings );
	}

	/**
	 * Get all webinars from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {

		/** @var Thrive_Dash_Api_GoToWebinar $api */
		$api   = $this->get_api();
		$lists = array();

		try {
			$all = $api->getUpcomingWebinars();

			foreach ( $all as $item ) {

				$lists [] = array(
					'id'   => $item['webinarKey'],
					'name' => $item['subject'],
				);
			}

			return $lists;
		} catch ( Thrive_Dash_Api_GoToWebinar_Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}
	}
}
