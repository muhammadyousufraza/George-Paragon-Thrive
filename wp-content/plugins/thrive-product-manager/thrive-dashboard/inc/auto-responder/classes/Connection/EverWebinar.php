<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_EverWebinar extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'webinar';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return 'EverWebinar';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'everwebinar' );
	}

	/**
	 * @return mixed|Thrive_Dash_List_Connection_Abstract
	 *
	 */
	public function read_credentials() {
		$key = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid EverWebinar key', 'thrive-dash' ) );
		}

		$this->set_credentials( array( 'key' => $key ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to EverWebinar using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'EverWebinar connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string
	 */
	public function test_connection() {
		try {
			$webinars = $this->get_api()->get_webinars();
			if ( ! $webinars ) {
				return false;
			}

			return true;
		} catch ( Thrive_Dash_Api_EverWebinar_Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Compose name from email address
	 *
	 * @param        $email_address
	 * @param string $split
	 *
	 * @return string
	 */
	public function nameFromEmail( $email_address, $split = '@' ) {
		return ucwords( str_replace( array(
			'_',
			'.',
			'-',
			'+',
			',',
			':',
		), ' ', strtolower( substr( $email_address, 0, strripos( $email_address, $split ) ) ) ) );
	}

	/**
	 * add contact to list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|mixed|Thrive_Dash_List_Connection_Abstract
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		$args = array( 'schedule' => 0 );
		if ( is_array( $arguments ) ) {

			if ( isset( $arguments['everwebinar_schedule'] ) ) {
				$args['schedule'] = $arguments['everwebinar_schedule'];
			}

			if ( isset( $arguments['email'] ) && ! empty( $arguments['email'] ) ) {
				$args['email'] = $arguments['email'];
			}

			if ( isset( $arguments['name'] ) && ! empty( $arguments['name'] ) ) {
				list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

				$args['first_name'] = $first_name;
				$args['last_name']  = $last_name;
			}

			if ( ! empty( $args['email'] ) && empty( $arguments['name'] ) ) {
				// First name is a required param, so we are building it for register forms with only email input
				$args['first_name'] = $this->nameFromEmail( $args['email'] );
			}

			if ( isset( $arguments['phone'] ) && ! empty( $arguments['phone'] ) ) {
				$args['phone'] = $arguments['phone'];
			}
		}

		try {

			$api    = $this->get_api();
			$webnar = $api->get_webinar_schedules( array( 'webinar_id' => $list_identifier ) );

			if ( isset( $webnar['schedules'] ) ) {
				$schedules = array_values( $webnar['schedules'] );

				$args['schedule'] = $schedules[0]['schedule_id'];
			}

			$api->register_to_webinar( $list_identifier, $args );
		} catch ( Exception $e ) {
			return $this->error( $e->getMessage() );
		}

		return true;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function get_extra_settings( $params = array() ) {
		$webinar_id = '';
		try {
			// Used on webinar select/change ajax [in admin Lead generation]
			if ( isset( $params['webinar_id'] ) ) {
				$webinar_id = $params['webinar_id'];
			} else {
				$webinars = $this->get_api()->get_webinars();
				if ( is_array( $webinars ) && isset( $webinars[0]['id'] ) ) {
					$webinar_id = $webinars[0]['id'];
				}
			}

			$params = $this->get_api()->get_webinar_schedules( array( 'webinar_id' => $webinar_id ) );
		} catch ( Thrive_Dash_Api_EverWebinar_Exception $e ) {
		}

		return $params;
	}

	/**
	 * @return mixed|Thrive_Dash_Api_EverWebinar
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_EverWebinar( array(
				'apiKey' => $this->param( 'key' ),
			)
		);
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {
		/** @var Thrive_Dash_Api_EverWebinar $ever_webinar */
		$ever_webinar = $this->get_api();

		try {
			return $ever_webinar->get_webinars();
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}
	}
}
